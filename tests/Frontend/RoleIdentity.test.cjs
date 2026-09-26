const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

const read = file => fs.readFileSync(path.join(__dirname, '../../resources/js', file), 'utf8')
const types = new Proxy({}, { get: (_, key) => key })

function loadActions(file, extra) {
  const scope = { module: { exports: {} }, types, userTypes: types, rootTypes: types,
    companyTypes: types, currencyTypes: types, preferencesTypes: types, ...extra }
  let source = read(file).replace(/^import .*$/gm, '')
  if (source.includes('export default')) source = source.replace('export default', 'module.exports =')
  else source = source.replace(/export const /g, 'const ') + '\nmodule.exports = { logout }'
  vm.runInNewContext(source, scope)
  return scope.module.exports
}

test('identity refresh accepts server role and refuses a response from a previous session', async () => {
  let token = 'first-session'
  const writes = []
  const commits = []
  let resolveResponse
  const actions = loadActions('store/actions.js', {
    Ls: { get: () => token, set: (...args) => writes.push(args) },
    window: { axios: { get: () => new Promise(resolve => { resolveResponse = resolve }) } }
  })
  const context = { state: { isAppLoaded: true, user: { currentUser: { role: 'admin' } } }, commit: (...args) => commits.push(args) }
  let pending = actions.refreshCurrentUser(context)
  resolveResponse({ data: { id: 42, role: 'accountant' } })
  await pending
  assert.equal(commits[0][1].role, 'accountant')
  assert.deepEqual(writes, [['role', 'accountant']])
  pending = actions.refreshCurrentUser(context)
  token = 'second-session'
  resolveResponse({ data: { id: 99, role: 'admin' } })
  await assert.rejects(pending, /Session changed/)
  assert.equal(commits.length, 1)
})

test('local logout clears cached identity, role, company and loaded state', () => {
  const removed = [], commits = [], destinations = []
  const actions = loadActions('store/modules/auth/actions.js', {
    Ls: { remove: key => removed.push(key) }, router: { push: path => destinations.push(path) }
  })
  actions.logout({ commit: (...args) => commits.push(args) }, true)
  assert.deepEqual(removed, ['auth.token', 'role', 'selectedCompany'])
  assert.ok(commits.some(([name]) => name === 'user/RESET_CURRENT_USER'))
  assert.ok(commits.some(([name, value]) => name === 'UPDATE_APP_LOADING_STATUS' && value === false))
  assert.deepEqual(destinations, ['/login'])
})

test('non-admin navigation never exposes administration menu entries', () => {
  const descriptor = parse(read('views/layouts/partials/TheSiteSidebar.vue')).descriptor
  const scope = { module: { exports: {} }, Calculator: {}, ScientificCalculator: {}, defineAsyncComponent: () => ({}) }
  vm.runInNewContext(descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), scope)
  const sidebar = scope.module.exports
  for (const role of ['admin', 'accountant', 'estimate', 'dispatch', 'Admin', 'unknown']) {
    const context = { ...sidebar.data(), role }
    const routes = sidebar.computed.visibleMenuSections.call(context).flatMap(section => section.items.map(item => item.route))
    for (const route of ['/users', '/settings', '/audit-logs']) assert.equal(routes.includes(route), role === 'admin', `${role}: ${route}`)
  }
})
