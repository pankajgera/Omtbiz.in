const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

function invoiceMethods(answer) {
  const navigations = [], errors = []
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/invoices/Create.vue'), 'utf8')
  const context = {
    module: { exports: {} }, InvoiceInventory: {}, MultiSelect: {}, draggable: {}, validationMixin: {},
    mapActions: () => ({}), mapGetters: () => ({}),
    swal: async () => answer,
    window: {
      location: { assign: url => navigations.push(url) },
      toastr: { error: message => errors.push(message) },
      printJS: () => assert.fail('Hidden-frame printing must not swallow the confirmation'),
      open: () => assert.fail('Printing must not depend on a popup')
    }
  }
  vm.runInNewContext(parse(source).descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), context)
  const form = { ...context.module.exports.methods, $t: key => key, reset: () => assert.fail('Do not reload before the PDF opens') }
  return { form, navigations, errors }
}

test('OK opens the saved invoice PDF even when hidden-frame printing is available', async () => {
  const { form, navigations } = invoiceMethods(true)
  await form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
  assert.deepEqual(navigations, ['/reports/invoice/saved-invoice-token?preview=1'])
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
