const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const moment = require('moment')
const { parse } = require('@vue/compiler-sfc')

function fixture () {
  const requests = [], errors = [], timers = new Map()
  let timerId = 0
  const scope = {
    module: { exports: {} }, moment, whatsappIconUrl: '', required: () => true,
    mapActions: () => ({}), mapGetters: () => ({}),
    window: { toastr: { error: message => errors.push(message) } },
    clearTimeout: id => timers.delete(id),
    setTimeout: callback => { timers.set(++timerId, callback); return timerId },
    createReportShare: (type, parameters) => new Promise((resolve, reject) => {
      requests.push({ type, parameters, resolve, reject })
    })
  }
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/reports/CustomersReport.vue'), 'utf8')
  vm.runInNewContext(parse(source).descriptor.script.content.replace(/^import .*$/gm, '')
    .replace('export default', 'module.exports ='), scope)
  const component = scope.module.exports
  const state = component.data()
  for (const [name, method] of Object.entries(component.methods)) state[name] = method.bind(state)
  state.vRange = { $touch () {} }
  state.vFormData = { $touch () {} }
  state.v$ = { $invalid: false }
  state.$t = key => key
  return { state, requests, errors, timers,
    unmount: () => component.unmounted.call(state),
    flush: () => [...timers].map(([id, callback]) => { timers.delete(id); return callback() }) }
}

test('ledger selection requests the current month immediately with no duplicate timer', async () => {
  const { state, requests, timers } = fixture()
  const pending = state.onLedgerSelected({ id: 106 })
  assert.equal(requests.length, 1)
  assert.equal(timers.size, 0)
  assert.equal(requests[0].parameters.ledger_id, 106)
  assert.equal(requests[0].parameters.from_date, moment().startOf('month').format('DD/MM/YYYY'))
  assert.equal(requests[0].parameters.to_date, moment().endOf('month').format('DD/MM/YYYY'))
  assert.equal(state.isReportLoading, true)
  requests[0].resolve('/current-month')
  await pending
  state.onReportLoaded()
  assert.equal(state.url, '/current-month')
  assert.equal(state.isReportLoading, false)
})

test('date changes debounce, discard old responses during the delay, and do not reload forever', async () => {
  const { state, requests, timers, flush } = fixture()
  const old = state.onLedgerSelected({ id: 106 })
  state.selectedRange = 'Today'
  state.onChangeDateRange()
  state.selectedRange = 'Previous Month'
  state.onChangeDateRange()
  assert.equal(timers.size, 1)
  requests[0].resolve('/old-filter')
  await old
  assert.equal(state.url, null)
  const [pending] = flush()
  assert.equal(requests.length, 2)
  assert.equal(requests[1].parameters.from_date, moment().subtract(1, 'month').startOf('month').format('DD/MM/YYYY'))
  requests[1].resolve('/previous-month')
  await pending
  assert.equal(state.url, '/previous-month')
  assert.equal(timers.size, 0)
})

test('stale failures do not interrupt a newer ledger and current failures allow retry', async () => {
  const { state, requests, errors } = fixture()
  const old = state.onLedgerSelected({ id: 106 })
  const current = state.onLedgerSelected({ id: 275 })
  requests[0].reject(new Error('Stale failure'))
  await old
  assert.equal(state.isReportLoading, true)
  assert.equal(errors.length, 0)
  requests[1].reject(new Error('Current failure'))
  await current
  assert.equal(state.isReportLoading, false)
  assert.equal(errors.length, 1)
  const retry = state.getReports()
  requests[2].resolve('/retry')
  await retry
  assert.equal(state.url, '/retry')
})

test('invalid automatic filters stay quiet, and leaving the page cancels scheduled and in-flight previews', async () => {
  const { state, requests, errors, timers, flush, unmount } = fixture()
  const old = state.onLedgerSelected({ id: 106 })
  state.v$.$invalid = true
  state.invalidateReport()
  await Promise.all(flush())
  assert.equal(requests.length, 1)
  assert.equal(errors.length, 0)
  requests[0].resolve('/invalidated')
  await old
  assert.equal(state.url, null)
  state.v$.$invalid = false
  const pending = state.getReports()
  state.invalidateReport()
  unmount()
  assert.equal(timers.size, 0)
  requests[1].resolve('/unmounted')
  await pending
  assert.equal(state.url, null)
})
