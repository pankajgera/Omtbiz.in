import { useVuelidate } from '@vuelidate/core'

export { useVuelidate }
export * from '@vuelidate/core'

export const validationMixin = {
  setup () {
    return { $v: useVuelidate() }
  }
}
