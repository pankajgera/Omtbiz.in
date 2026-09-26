const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const Vue = require('vue')
const { createStore, mapActions, mapGetters } = require('vuex')
const { parse } = require('@vue/compiler-sfc')
const { compile } = require('@vue/compiler-dom')
const { renderToString } = require('@vue/server-renderer')

const read = file => fs.readFileSync(path.join(__dirname, '../../resources/js', file), 'utf8')
const slotStub = { render() { return Vue.h('div', [this.$slots.activator?.(), this.$slots.default?.()]) } }

async function renderUsers(role) {
  const { descriptor } = parse(read('views/users/Index.vue'))
  const context = {
    module: { exports: {} }, mapActions, mapGetters,
    SweetModal: slotStub, SweetModalTab: slotStub, DotIcon: slotStub,
    AstronautIcon: slotStub, BaseButton: slotStub, BaseLoader: slotStub, TablePagination: slotStub
  }
  vm.runInNewContext(descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), context)
  const component = context.module.exports
  component.render = new Function('Vue', compile(descriptor.template.content, { mode: 'function', prefixIdentifiers: true }).code)(Vue)
  const app = Vue.createSSRApp(component)
  app.config.globalProperties.$t = app.config.globalProperties.$tc = key => key
  app.use(createStore({ modules: { user: { namespaced: true, getters: {
    currentUser: () => role == null ? null : { role },
    users: () => [{ id: 42, name: 'Sample User', role: 'accountant' }],
    selectedUsers: () => [42], totalUsers: () => 1, selectAllField: () => false
  } } } }))
  for (const name of ['Header', 'v-dropdown', 'v-dropdown-item', 'base-input', 'base-select', 'font-awesome-icon']) app.component(name, slotStub)
  app.component('router-link', { props: ['to'], render() { return Vue.h('a', { href: typeof this.to === 'string' ? this.to : this.to.path }, this.$slots.default?.()) } })
  return renderToString(app)
}

test('only admins see create, edit, delete and bulk selection controls', async () => {
  const admin = await renderUsers('admin')
  for (const text of ['users.new_user', 'general.edit', 'general.delete', 'users/42/edit', 'select-all-users']) assert.ok(admin.includes(text), text)
  for (const role of ['accountant', 'estimate', 'dispatch', 'customer', null]) {
    const html = await renderUsers(role)
    for (const text of ['users.new_user', 'general.edit', 'general.delete', 'users/42/edit', 'select-all-users']) assert.ok(!html.includes(text), `${role}: ${text}`)
    assert.ok(html.includes('Sample User'))
  }
})

test('actual router metadata blocks direct user URLs and trusts the loaded role over cached admin', async () => {
  const { createRouter, createMemoryHistory } = await import('vue-router')
  let guard
  const store = { getters: { 'auth/isAuthenticated': true, 'user/currentUser': { role: 'accountant' } }, dispatch: async () => {} }
  const context = {
    module: { exports: {} }, store, Ls: { get: () => 'admin' }, LayoutBasic: {}, LayoutLogin: {},
    createWebHistory: createMemoryHistory,
    createRouter: options => {
      const router = createRouter(options)
      router.beforeEach = callback => { guard = callback }
      return router
    }
  }
  vm.runInNewContext(read('router.js').replace(/^import .*$/gm, '').replace('export default router', 'module.exports = router'), context)
  const router = context.module.exports
  for (const url of ['/users', '/users/create', '/users/42/edit', '/settings/user-profile', '/customers/create', '/customers/42/edit']) {
    assert.equal(await guard(router.resolve(url)), '/invoices/create', url)
  }
  assert.equal(await guard(router.resolve('/invoices/create')), undefined)
  assert.equal(await guard(router.resolve('/customers')), undefined)
  store.getters['user/currentUser'] = { role: 'admin' }
  assert.equal(await guard(router.resolve('/users/create')), undefined)
  store.getters['user/currentUser'] = { role: 'unknown' }
  assert.equal(await guard(router.resolve('/users/create')), '/login')
  assert.equal(await guard(router.resolve('/login')), undefined)

  // Cold load: cached admin must not admit a protected screen before hydration.
  store.getters['user/currentUser'] = null
  store.dispatch = async () => { store.getters['user/currentUser'] = { role: 'accountant' } }
  for (const url of ['/users', '/settings', '/settings/expense-category']) {
    assert.equal(await guard(router.resolve(url)), '/invoices/create', url)
  }

  // Every registered restricted route checks every supported and unknown role.
  for (const role of ['admin', 'accountant', 'estimate', 'dispatch', 'customer', 'Admin', 'unknown']) {
    store.dispatch = async () => { store.getters['user/currentUser'] = { role } }
    for (const route of router.getRoutes()) {
      const target = router.resolve(route.path.replace(/:id/g, '42'))
      if (!target.matched.some(record => Array.isArray(record.meta))) continue
      const allowed = target.matched.every(record => !Array.isArray(record.meta) || record.meta.includes(role))
      const result = await guard(target)
      assert.equal(result === undefined, allowed, `${role}: ${route.path}`)
    }
  }

  // Server demotion overrides even an already hydrated admin.
  store.getters['user/currentUser'] = { role: 'admin' }
  store.dispatch = async () => { store.getters['user/currentUser'] = { role: 'dispatch' } }
  assert.equal(await guard(router.resolve('/users')), '/dispatch/create')
  store.dispatch = async action => { if (action === 'refreshCurrentUser') throw new Error('Offline') }
  assert.equal(await guard(router.resolve('/users')), '/login')
})
