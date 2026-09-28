const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

function invoiceMethods(answer, blocked = false) {
  const navigations = [], errors = [], openedTabs = []
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/invoices/Create.vue'), 'utf8')
  const context = {
    module: { exports: {} }, InvoiceInventory: {}, MultiSelect: {}, draggable: {}, validationMixin: {},
    mapActions: () => ({}), mapGetters: () => ({}),
    swal: async () => answer,
    i18n: { global: { t: key => key } },
    window: {
      location: { assign: () => assert.fail('The form tab must stay open') },
      toastr: { error: message => errors.push(message) },
      printJS: () => assert.fail('Hidden-frame printing must not swallow the confirmation'),
      open: (url, target) => {
        openedTabs.push({ url, target })
        return blocked ? null : { location: { replace: url => navigations.push(url) } }
      }
    }
  }
  const helper = fs.readFileSync(path.join(__dirname, '../../resources/js/helpers/reportTabs.js'), 'utf8')
  vm.runInNewContext(helper.replace(/^import .*$/gm, '').replace('export async function', 'async function'), context)
  vm.runInNewContext(parse(source).descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), context)
  const form = { ...context.module.exports.methods, $t: key => key, reset: () => assert.fail('Do not reload before the PDF opens') }
  return { form, navigations, errors, openedTabs }
}

test('OK opens the saved invoice preview before resetting for a new invoice', async () => {
  const { form, navigations, openedTabs } = invoiceMethods(true)
  let resets = 0
  form.reset = () => {
    assert.deepEqual(navigations, ['/reports/invoice/saved-invoice-token?preview=1'])
    resets++
  }
  assert.equal(await form.showInvoicePopup({ unique_hash: 'saved-invoice-token' }), true)
  assert.deepEqual(navigations, ['/reports/invoice/saved-invoice-token?preview=1'])
  assert.deepEqual(openedTabs, [{ url: 'about:blank', target: '_blank' }])
  assert.equal(resets, 1)
})

test('a blocked report tab shows an error without navigating away', async () => {
  const { form, navigations, errors } = invoiceMethods(true, true)
  await form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
  assert.deepEqual(navigations, [])
  assert.deepEqual(errors, ['reports.popup_blocked'])
})

test('invoice slips also open in a new tab without resetting the form', async () => {
  const { form, navigations, openedTabs } = invoiceMethods(true)
  await form.printSlip('saved-invoice-token')
  assert.deepEqual(navigations, ['/reports/slip/saved-invoice-token'])
  assert.equal(openedTabs[0].target, '_blank')
})

test('Cancel keeps the existing reset behavior without opening a PDF', async () => {
  const { form, navigations } = invoiceMethods(false)
  let reset = false
  form.reset = () => { reset = true }
  await form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
  assert.equal(reset, true)
  assert.deepEqual(navigations, [])
})

test('a missing PDF token produces a visible error without losing the form', async () => {
  for (const invoice of [{}, { unique_hash: '' }, { unique_hash: ' ' }, undefined]) {
    const { form, navigations, errors } = invoiceMethods(true)
    await form.showInvoicePopup(invoice)
    assert.deepEqual(navigations, [])
    assert.deepEqual(errors, ['invoices.print_error'])
  }
})
