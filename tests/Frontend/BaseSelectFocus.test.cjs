const assert = require('node:assert/strict')
const { test } = require('node:test')
const fs = require('node:fs')
const path = require('node:path')
const vm = require('node:vm')
const { parse } = require('@vue/compiler-sfc')

const source = fs.readFileSync(path.join(__dirname, '../../resources/js/components/base/base-select/BaseSelect.vue'), 'utf8')
const script = parse(source).descriptor.script.content
  .replace(/^import .*$/gm, '')
  .replace('export default', 'module.exports =')
const context = { module: { exports: {} }, multiselectMixin: {}, pointerMixin: {} }
vm.runInNewContext(script, context)
const select = context.module.exports

function focusCount(props = {}) {
  let count = 0
  select.methods.focusInput.call({
    name: '', autofocus: select.props.autofocus.default, disabled: false,
    $refs: { search: { focus: () => count++ } }, ...props
  })
  return count
}

test('an explicit opt-out prevents party autofocus from competing with page focus', () => {
  assert.equal(focusCount({ name: 'party_name', autofocus: false }), 0)
})

test('existing party selectors retain their default autofocus', () => {
  assert.equal(focusCount({ name: 'party_name' }), 1)
  assert.equal(focusCount({ name: 'estimate' }), 0)
})

test('explicit autofocus works for other enabled selectors only', () => {
  assert.equal(focusCount({ autofocus: true }), 1)
  assert.equal(focusCount({ autofocus: true, disabled: true }), 0)
  assert.equal(focusCount({ autofocus: true, $refs: {} }), 0)
})
