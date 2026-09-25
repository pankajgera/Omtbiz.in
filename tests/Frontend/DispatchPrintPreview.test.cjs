const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

// Exercise the component methods with browser/notification side effects stubbed.
function component(file, overrides = {}) {
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/dispatch', file), 'utf8')
  const script = parse(source).descriptor.script.content
    .replace(/^import .*$/gm, '')
    .replace('export default', 'module.exports =')
  const context = {
    module: { exports: {} }, mapActions: () => ({}), required: () => true,
    VueTimepicker: {}, DispatchPrintPreview: {}, ...overrides
  }
  vm.runInNewContext(script, context)
  return context.module.exports
}

test('confirming after save opens a preview before any navigation', async () => {
  const events = []
  const page = component('Create.vue', { swal: async () => true })
  const state = {
    ...page.methods, $t: key => key,
    $nextTick: async () => events.push('render'),
    $refs: { printPreview: { open: () => events.push('preview') } },
    $router: { push: () => events.push('navigate') }
  }
  await state.showDispatchPopup()
  assert.deepEqual(events, ['render', 'preview'])
  state.closePrintPreview()
  assert.deepEqual(events, ['render', 'preview', 'navigate'])
})

test('cancelling the confirmation returns to the list without printing', async () => {
  const page = component('Create.vue', { swal: async () => false })
  const routes = []
  await page.methods.showDispatchPopup.call({
    $t: key => key, $router: { push: route => routes.push(route) },
    printDispatch: () => assert.fail('Cancelled print should not open')
  })
  assert.deepEqual(routes, ['/dispatch'])
})

test('closing a reprint preview stays on the edit page and keeps the saved time', () => {
  const page = component('Create.vue')
  page.methods.closePrintPreview.call({ returnAfterPrint: false, $router: { push: () => assert.fail('Unexpected navigation') } })
  const invoice = { id: 10 }
  const state = { formData: { invoice_id: [10], time: '09:15 AM' }, invoiceList: [invoice] }
  page.methods.loadInvoice.call(state)
  assert.equal(state.formData.time, '09:15 AM')
  assert.equal(state.invoice[0], invoice)
})

test('printing uses the preview document without a navigation callback', () => {
  const document = {}
  let options
  const preview = component('DispatchPrintPreview.vue', { printJS: value => { options = value } })
  const state = { $refs: { document }, $t: key => key, printError: false }
  preview.methods.print.call(state)
  assert.equal(options.printable, document)
  assert.equal(options.type, 'html')
  assert.equal(options.scanStyles, false)
  assert.equal(options.onPrintDialogClose, undefined)
  options.onError(new Error('Unavailable'))
  assert.equal(state.printError, true)
})

test('a print failure is shown rather than closing the preview', () => {
  const preview = component('DispatchPrintPreview.vue', { printJS: () => { throw new Error('Unavailable') } })
  const state = { $refs: { document: {} }, $t: key => key, printError: false }
  preview.methods.print.call(state)
  assert.equal(state.printError, true)
})
