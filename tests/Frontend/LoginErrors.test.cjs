const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')
const { compile } = require('@vue/compiler-dom')
const Vue = require('vue')
const { renderToString } = require('@vue/server-renderer')
const messages = require('../../resources/js/plugins/en.json').login
const read = file => fs.readFileSync(path.join(__dirname, '../../resources/js', file), 'utf8')
const descriptor = parse(read('views/auth/Login.vue')).descriptor
const context = {
  module: { exports: {} }, mapActions: () => ({}),
  IconFacebook: {}, IconTwitter: {}, IconGoogle: {}, Ls: { get: () => 'admin' }
}
vm.runInNewContext(descriptor.script.content.replace(/^import .*$/gm, '').replace('export default', 'module.exports ='), context)
const login = context.module.exports

function state(overrides = {}) {
  return {
    customError: '', isLoading: false, loginData: {},
    v$: { $touch() {}, $invalid: false },
    $t: key => messages[key.split('.').at(-1)],
    $router: { push: () => assert.fail('A failed login must stay on the form') },
    ...overrides
  }
}

for (const [name, error, expected] of [
  ['Passport credentials', { response: { status: 400, data: { error: 'invalid_grant', error_description: 'The user credentials were incorrect.' } } }, messages.invalid_credentials],
  ['legacy credentials', { response: { status: 401, data: { error: 'invalid_credentials' } } }, messages.invalid_credentials],
  ['validation', { response: { status: 422, data: { errors: { username: ['The username must be a valid email address.'] } } } }, 'The username must be a valid email address.'],
  ['rate limit', { response: { status: 429, data: {} } }, messages.too_many_attempts],
  ['network', new Error('Network Error'), messages.connection_error],
  ['server failure', { response: { status: 500, data: { message: 'Internal database details' } } }, messages.failed],
  ['API description', { response: { status: 403, data: { error_description: 'Login is unavailable for this account.' } } }, 'Login is unavailable for this account.']
]) {
  test(`${name} produces visible error text and resets the loading state`, async () => {
    const form = state({ login: async () => { throw error } })
    await login.methods.validateBeforeSubmit.call(form)
    assert.equal(form.customError, expected)
    assert.equal(form.isLoading, false)
  })
}

test('validation and repeated clicks cannot issue login requests', async () => {
  for (const overrides of [{ v$: { $touch() {}, $invalid: true } }, { isLoading: true }]) {
    const form = state({ login: () => assert.fail('Unexpected request'), ...overrides })
    await login.methods.validateBeforeSubmit.call(form)
  }
})

test('a successful retry clears the prior error and preserves admin navigation', async () => {
  const destinations = []
  const form = state({ customError: 'Previous error', login: async () => ({}), $router: { push: value => destinations.push(value) } })
  await login.methods.validateBeforeSubmit.call(form)
  assert.equal(form.customError, '')
  assert.equal(form.isLoading, false)
  assert.deepEqual(destinations, ['/invoices/create'])
})

test('login errors render once as an accessible alert', async () => {
  const component = {
    ...login, setup: undefined,
    data: () => ({ ...login.data(), customError: messages.invalid_credentials, v$: { loginData: { email: { $error: false }, password: { $error: false } } } }),
    render: new Function('Vue', compile(descriptor.template.content, { mode: 'function', prefixIdentifiers: true }).code)(Vue)
  }
  const app = Vue.createSSRApp(component)
  app.config.globalProperties.$t = key => key
  for (const name of ['base-input', 'base-button', 'router-link']) app.component(name, { render() { return Vue.h('div', this.$slots.default?.()) } })
  const html = await renderToString(app)
  assert.match(html, /id="login-error"[^>]*role="alert"/)
  assert.equal(html.split(messages.invalid_credentials).length - 1, 1)
})

test('the HTTP interceptor preserves login/network failures and rejects expired-session responses', async () => {
  const logouts = []
  const scope = { module: { exports: {} }, store: { dispatch: (...args) => logouts.push(args) } }
  vm.runInNewContext(read('bootstrap.js').replace(/^import .*$/gm, '').replace(/export function /g, 'function ') + '\nmodule.exports = handleResponseError', scope)
  for (const error of [
    { config: { url: '/api/auth/login' }, response: { status: 401 } },
    new Error('Network Error'),
    { config: { url: '/api/users' }, response: { status: 401 } }
  ]) {
    await assert.rejects(scope.module.exports(error), actual => actual === error)
  }
  assert.deepEqual(logouts, [['auth/logout', true]])
})
