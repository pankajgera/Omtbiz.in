const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

function fixture () {
  const events = [], errors = []
  const tab = {
    opener: {}, closed: false,
    location: { replace: url => events.push(['navigate', url]) },
    close () { this.closed = true; events.push(['close']) }
  }
  const scope = {
    i18n: { global: { t: key => key } },
    window: {
      open: (url, target) => { events.push(['open', url, target]); return tab },
      toastr: { error: text => errors.push(text) }
    },
    module: { exports: {} }, mapActions: () => ({}), mapGetters: () => ({}), required: () => true
  }
  const helper = fs.readFileSync(path.join(__dirname, '../../resources/js/helpers/reportTabs.js'), 'utf8')
  vm.runInNewContext(helper.replace(/^import .*$/gm, '').replace('export async function', 'async function'), scope)
  return { events, errors, tab, scope, open: scope.openReportInNewTab }
}

test('reserves a detached tab before requesting a link and waits for the response', async () => {
  const { open, events, tab } = fixture()
  let resolve
  const pending = open(() => {
    events.push(['request'])
    return new Promise(done => { resolve = done })
  })
  assert.deepEqual(events, [['open', 'about:blank', '_blank'], ['request']])
  assert.equal(tab.opener, null)
  resolve('/reports/customers/saved-token')
  assert.equal(await pending, true)
  assert.deepEqual(events.at(-1), ['navigate', '/reports/customers/saved-token'])
})

test('invalid filters and failed requests close the reserved tab without losing the current page', async () => {
  for (const fail of [false, true]) {
    const { open, tab, events, errors } = fixture()
    assert.equal(await open(async () => {
      if (fail) throw new Error('Request failed')
      return null
    }), false)
    assert.equal(tab.closed, true)
    assert.equal(events.some(event => event[0] === 'navigate'), false)
    assert.deepEqual(errors, fail ? ['reports.open_failed'] : [])
  }
})

test('closing the reserved tab while loading does not reopen it or navigate the current page', async () => {
  const { open, events, tab } = fixture()
  let resolve
  const pending = open(() => new Promise(done => { resolve = done }))
  tab.closed = true
  resolve('/reports/customers/saved-token')
  assert.equal(await pending, false)
  assert.equal(events.length, 1)
})

for (const file of ['BanksReport.vue', 'ExpensesReport.vue', 'ProfitLossReport.vue', 'SalesReports.vue']) {
  for (const action of ['viewReportsPDF', 'downloadReport']) {
    test(`${file} ${action} reserves a new tab before loading the report`, async () => {
      const { scope, events } = fixture()
      const source = fs.readFileSync(path.join(__dirname, '../../resources/js/views/reports', file), 'utf8')
      vm.runInNewContext(parse(source).descriptor.script.content.replace(/^import .*$/gm, '')
        .replace('export default', 'module.exports ='), scope)
      let resolve
      const state = {
        getReportUrl: '/reports/test/saved-token',
        getReports: () => { events.push(['request']); return new Promise(done => { resolve = done }) }
      }
      const pending = scope.module.exports.methods[action].call(state)
      assert.deepEqual(events, [['open', 'about:blank', '_blank'], ['request']])
      resolve(true)
      assert.equal(await pending, true)
      assert.deepEqual(events.at(-1), ['navigate', state.getReportUrl + (action === 'downloadReport' ? '?download=true' : '')])
    })
  }
}
