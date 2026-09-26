const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')
const validators = require('@vuelidate/validators')

function component(file, extra = {}) {
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js', file), 'utf8')
  const script = parse(source).descriptor.script.content
    .replace(/^import .*$/gm, '').replace('export default', 'module.exports =')
  const context = { module: { exports: {} }, ...validators, MultiSelect: {}, GlobalMixin: {},
    mapActions: (_module, actions) => Object.fromEntries(actions.map(action => [action, function (id) {
      return this.$store.dispatch(`${_module}/${action}`, id)
    }])), mapGetters: () => ({}), ...extra }
  vm.runInNewContext(script, context)
  return context.module.exports
}

test('user edit selects company by ID even when the saved company name is missing', async () => {
  const form = component('views/users/Create.vue')
  const company = { id: 1, name: 'Current Company' }
  const state = { formData: {}, $route: { params: { id: 2 } },
    fetchUser: async () => ({ data: { user: { id: 2, company_id: 1, company_name: null, role: 'admin' },
      companies: [company], roles: [{ name: 'admin' }] } }) }
  await form.methods.loaduser.call(state)
  assert.equal(state.companyBind, company)
  form.watch.companyBind.call(state, company)
  assert.equal(state.formData.company, 'Current Company')
  form.watch.companyBind.call(state, undefined)
  form.watch.roleBind.call(state, null)
  assert.equal(state.formData.company, null)
  assert.equal(state.formData.role, null)
})

test('user edit allows an unchanged password but validates a new password confirmation', () => {
  const form = component('views/users/Create.vue')
  const unchanged = form.validations.call({ isEdit: true, isRequired: false, formData: { password: '' } }).formData
  assert.equal(unchanged.password.required.$validator(''), true)
  const changed = form.validations.call({ isEdit: true, isRequired: true, formData: { password: 'new-password' } }).formData
  assert.equal(changed.confirm_password.sameAsPassword.$validator('different'), false)
  assert.equal(changed.confirm_password.sameAsPassword.$validator('new-password'), true)
})

test('invoice-linked receipt loads the party and amount through the invoice store action', async () => {
  const form = component('views/receipts/Create.vue')
  const party = { id: 42, name: 'Invoice Party' }
  const state = { $route: { params: { id: 123 } }, formData: {}, sundryDebtorList: [party],
    $store: { dispatch: async (action, id) => {
      assert.equal(action, 'invoice/fetchInvoice')
      assert.equal(id, 123)
      return { data: { invoice: { id, account_master_id: 42, due_amount: 120.5 } } }
    } } }
  state.fetchInvoice = form.methods.fetchInvoice.bind(state)
  await form.methods.setInvoiceReceiptData.call(state)
  assert.equal(state.formData.list, party)
  assert.equal(state.formData.invoice_id, 123)
  assert.equal(state.formData.amount, '120.50')
  assert.equal(state.maxPayableAmount, 120.5)
  assert.equal(form.computed.openingBalance.call({ formData: { list: party }, accountLedger: [] }), '0.00')
})

test('date picker emits Vue 3 model updates when selecting and clearing a date', () => {
  const picker = component('components/base/base-date-picker/BaseDatePicker.vue', {
    DateInput: {}, PickerDay: {}, PickerMonth: {}, PickerYear: {}, utils: {},
  })
  const events = []
  const state = { setPageDate: () => {}, $emit: (...event) => events.push(event) }
  picker.methods.setDate.call(state, Date.UTC(2026, 8, 26))
  assert.equal(events.find(e => e[0] === 'update:modelValue')[1].toISOString(), '2026-09-26T00:00:00.000Z')
  events.length = 0
  picker.methods.clearDate.call(state)
  assert.equal(events.find(e => e[0] === 'update:modelValue')[1], null)
})

test('receipt edit uses the numeric suffix separately from its prefix', async () => {
  const form = component('views/receipts/Create.vue')
  const state = { isEdit: true, $route: { params: { id: 13 } }, fetchReceipt: async () => ({ data: {
    receipt: { receipt_number: 'REC-002349', amount: '15', account_master_id: 42, invoice: null },
    receipt_prefix: 'REC', nextReceiptNumber: '002349', nextReceiptNumberAttribute: 'REC-002349',
    usersOfSundryDebitors: [{ id: 42 }], account_ledger: [], receipt_mode: [],
  } }) }
  await form.methods.loadData.call(state)
  assert.equal(state.receiptPrefix, 'REC')
  assert.equal(state.receiptNumAttribute, '002349')
  assert.equal(validators.numeric.$validator(state.receiptNumAttribute), true)
})

test('bank Vuex state and actions share the list, selection and API contracts', async () => {
  const base = path.join(__dirname, '../../resources/js/store/modules/banks')
  function load(file, globals = {}) {
    const exports = []
    let source = fs.readFileSync(path.join(base, file + '.js'), 'utf8').replace(/^import .*$/gm, '')
    source = source.replace(/export const (\w+)/g, (_, name) => { exports.push(name); return `const ${name}` })
      .replace('export default', 'module.exports =')
    if (exports.length) source += `\nmodule.exports = { ${exports.join(',')} }`
    const context = { module: { exports: {} }, ...globals }
    vm.runInNewContext(source, context)
    return context.module.exports
  }
  const types = load('mutation-types')
  const mutations = load('mutations', { types })
  const getters = load('getters')
  const actions = load('actions', { types, window: { axios: { get: async (url) => {
    assert.equal(url, '/api/banks')
    return { data: { banks: { data: [{ id: 1 }], total: 1 } } }
  } } } })
  const { state } = load('index', { getters, actions, mutations })
  const context = { state, commit: (type, data) => mutations[type](state, data) }
  assert.equal(getters.banks(state).length, 0)
  assert.equal(getters.selectedBanks(state).length, 0)
  await actions.fetchBanks(context)
  assert.equal(getters.totalBanks(state), 1)
  actions.selectBank(context, [1])
  assert.equal(state.selectAllField, true)
  mutations[types.DELETE_MULTIPLE_BANKS](state, [1])
  assert.equal(state.banks.length, 0)
  actions.resetSelectedBank(context)
  assert.equal(state.selectedBanks.length, 0)
  assert.equal(state.selectAllField, false)
})

test('report date defaults use ISO strings without Moment fallback parsing', () => {
  const moment = require('moment')
  for (const file of ['SalesReports', 'ExpensesReport', 'ProfitLossReport', 'BanksReport', 'CustomersReport']) {
    const report = component(`views/reports/${file}.vue`, { moment, whatsappIconUrl: '' })
    const { formData } = report.data()
    for (const value of [formData.from_date, formData.to_date]) {
      assert.equal(moment(value, moment.ISO_8601, true).isValid(), true, file)
    }
  }
})
