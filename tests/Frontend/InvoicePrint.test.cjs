const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

function invoiceMethods(answer, options = {}) {
  const navigations = [], errors = [], openedTabs = [], frames = []
  let printCalls = 0
  const reportWindow = new EventTarget()
  reportWindow.focus = () => {}
  reportWindow.print = () => {
    if (options.printFails) throw new Error('Print unavailable')
    printCalls++
  }
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/invoices/Create.vue'), 'utf8')
  const context = {
    module: { exports: {} }, InvoiceInventory: {}, MultiSelect: {}, draggable: {}, validationMixin: {},
    mapActions: () => ({}), mapGetters: () => ({}),
    swal: async () => answer,
    setTimeout, clearTimeout,
    i18n: { global: { t: key => key } },
    document: {
      createElement: tag => {
        assert.equal(tag, 'iframe')
        return {
          style: {}, setAttribute() {}, removed: false,
          remove() { this.removed = true },
          contentWindow: reportWindow,
          contentDocument: { querySelector: () => !options.invalidReport, fonts: { ready: Promise.resolve() } }
        }
      },
      body: { appendChild: frame => frames.push(frame) }
    },
    window: {
      location: { assign: () => assert.fail('Do not redirect the form tab') },
      toastr: { error: message => errors.push(message) },
      open: (url, target) => {
        openedTabs.push({ url, target })
        return { location: { replace: url => navigations.push(url) } }
      }
    }
  }
  for (const file of ['reportTabs', 'printInvoiceReport']) {
    const helper = fs.readFileSync(path.join(__dirname, `../../resources/js/helpers/${file}.js`), 'utf8')
    vm.runInNewContext(helper.replace(/^import .*$/gm, '').replace('export ', ''), context)
  }
  vm.runInNewContext(parse(source).descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), context)
  const form = { ...context.module.exports.methods, $t: key => key, reset: () => assert.fail('Do not reset before printing finishes') }
  return { form, navigations, errors, openedTabs, frames, reportWindow, printCalls: () => printCalls }
}

for (const outcome of ['print', 'cancel']) {
  test(`OK prints in the current tab and resets only after ${outcome} closes the dialog`, async () => {
    const { form, frames, reportWindow, openedTabs, printCalls } = invoiceMethods(true)
    let resets = 0
    form.reset = () => { resets++ }
    const pending = form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
    await new Promise(resolve => setImmediate(resolve))
    assert.equal(frames.length, 1)
    assert.equal(frames[0].src, '/reports/invoice/saved-invoice-token?preview=1')
    assert.equal(resets, 0)
    await frames[0].onload()
    assert.equal(printCalls(), 1)
    assert.equal(resets, 0)
    assert.deepEqual(openedTabs, [])
    reportWindow.dispatchEvent(new Event('afterprint'))
    assert.equal(await pending, true)
    assert.equal(resets, 1)
    assert.equal(frames[0].removed, true)
    reportWindow.dispatchEvent(new Event('afterprint'))
    assert.equal(resets, 1)
  })
}

for (const options of [{ invalidReport: true }, { printFails: true }]) {
  test(`a report/print failure preserves the form: ${JSON.stringify(options)}`, async () => {
    const { form, frames, errors, openedTabs } = invoiceMethods(true, options)
    const pending = form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
    await new Promise(resolve => setImmediate(resolve))
    await frames[0].onload()
    assert.equal(await pending, false)
    assert.equal(frames[0].removed, true)
    assert.deepEqual(errors, ['invoices.print_failed'])
    assert.deepEqual(openedTabs, [])
  })
}

test('invoice slips retain their separate new-tab behavior', async () => {
  const { form, navigations, openedTabs } = invoiceMethods(true)
  await form.printSlip('saved-invoice-token')
  assert.deepEqual(navigations, ['/reports/slip/saved-invoice-token'])
  assert.equal(openedTabs[0].target, '_blank')
})

test('Cancel on the confirmation resets without loading or printing a report', async () => {
  const { form, frames, openedTabs } = invoiceMethods(false)
  let reset = false
  form.reset = () => { reset = true }
  await form.showInvoicePopup({ unique_hash: 'saved-invoice-token' })
  assert.equal(reset, true)
  assert.deepEqual(frames, [])
  assert.deepEqual(openedTabs, [])
})

test('a missing report token produces a visible error without losing the form', async () => {
  for (const invoice of [{}, { unique_hash: '' }, { unique_hash: ' ' }, undefined]) {
    const { form, frames, errors } = invoiceMethods(true)
    await form.showInvoicePopup(invoice)
    assert.deepEqual(frames, [])
    assert.deepEqual(errors, ['invoices.print_error'])
  }
})
