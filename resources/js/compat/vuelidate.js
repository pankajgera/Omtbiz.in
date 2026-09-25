import { useVuelidate } from '@vuelidate/core'

export { useVuelidate }
export * from '@vuelidate/core'

const createValidationProxy = (target = {}) => {
  const handler = {
    get (obj, prop) {
      if (prop in obj) {
        return obj[prop]
      }
      if (prop === '$error' || prop === '$invalid' || prop === '$dirty') {
        return false
      }
      if (prop === '$touch' || prop === '$reset') {
        return () => {}
      }
      return proxy
    }
  }
  const proxy = new Proxy(target, handler)
  return proxy
}

export const validationMixin = {
  data () {
    return { _vuelidate: null }
  },
  beforeCreate () {
    this._vuelidate = useVuelidate(undefined, undefined, { currentVueInstance: this })
  },
  computed: {
    $v () {
      const vuelidate = this._vuelidate?.value || this._vuelidate || {}
      return createValidationProxy(vuelidate)
    }
  }
}
