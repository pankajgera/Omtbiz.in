import { useVuelidate } from '@vuelidate/core'

export { useVuelidate }
export * from '@vuelidate/core'

export const validationMixin = {
  data () {
    return { _vuelidate: null }
  },
  beforeCreate () {
    this._vuelidate = useVuelidate(undefined, undefined, { currentVueInstance: this })
  },
  computed: {
    $v () {
      return this._vuelidate || {}
    }
  }
}
