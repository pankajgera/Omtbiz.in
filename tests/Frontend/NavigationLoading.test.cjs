const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { reactive } = require('vue')

const deferred = () => {
  let resolve
  const promise = new Promise(done => { resolve = done })
  return { promise, resolve }
}

async function fixture () {
  const { createRouter, createMemoryHistory } = await import('vue-router')
  const source = fs.readFileSync(path.join(__dirname, '../../resources/js/helpers/navigationLoader.js'), 'utf8')
  const timers = new Map()
  let timerId = 0
  const scope = { reactive, Date, module: { exports: {} }, window: {
    clearTimeout: id => timers.delete(id),
    setTimeout: callback => { timers.set(++timerId, callback); return timerId }
  } }
  vm.runInNewContext(source.replace(/^import .*$/gm, '').replace(/export /g, '') +
    '\nmodule.exports = { navigationLoader, installNavigationLoader }', scope)
  const { navigationLoader: loading, installNavigationLoader } = scope.module.exports
  const router = createRouter({ history: createMemoryHistory(), routes: [
    { path: '/', component: {} }, { path: '/notes', component: {} },
    { path: '/inventory', component: {} }, { path: '/reports', redirect: '/notes' },
    { path: '/broken', component: () => Promise.reject(new Error('Chunk unavailable')) }
  ] })
  installNavigationLoader(router)
  const flush = () => {
    for (const [id, callback] of [...timers]) { timers.delete(id); callback() }
  }
  await router.push('/')
  flush()
  return { router, loading, flush }
}

test('loader is visible during authorization and clears after navigation', async () => {
  const { router, loading, flush } = await fixture()
  const entered = deferred(), auth = deferred()
  router.beforeEach(() => { entered.resolve(); return auth.promise })
  const pending = router.push('/notes')
  await entered.promise
  assert.equal(loading.active, true)
  assert.equal(router.currentRoute.value.path, '/')
  auth.resolve()
  await pending
  flush()
  assert.equal(router.currentRoute.value.path, '/notes')
  assert.equal(loading.active, false)
  await router.push('/notes')
  flush()
  assert.equal(loading.active, false, 'same-page clicks must not leave a loader')
})

test('an older cancelled navigation cannot hide the latest navigation loader', async () => {
  const { router, loading, flush } = await fixture()
  const firstEntered = deferred(), secondEntered = deferred()
  const first = deferred(), second = deferred()
  router.beforeEach(to => {
    if (to.path === '/notes') { firstEntered.resolve(); return first.promise }
    secondEntered.resolve(); return second.promise
  })
  const oldNavigation = router.push('/notes')
  await firstEntered.promise
  const newNavigation = router.push('/inventory')
  await secondEntered.promise
  first.resolve()
  await oldNavigation
  flush()
  assert.equal(loading.active, true)
  second.resolve()
  await newNavigation
  flush()
  assert.equal(loading.active, false)
  assert.equal(router.currentRoute.value.path, '/inventory')
})

test('aborts, redirects and failed page imports clear the loader and allow retry', async () => {
  const { router, loading, flush } = await fixture()
  const removeGuard = router.beforeEach(() => false)
  await router.push('/notes')
  flush()
  assert.equal(loading.active, false)
  removeGuard()
  await router.push('/reports')
  flush()
  assert.equal(router.currentRoute.value.path, '/notes')
  assert.equal(loading.active, false)
  await assert.rejects(router.push('/broken'), /Chunk unavailable/)
  flush()
  assert.equal(loading.active, false)
  await router.push('/inventory')
  flush()
  assert.equal(loading.active, false)
  assert.equal(router.currentRoute.value.path, '/inventory')
})
