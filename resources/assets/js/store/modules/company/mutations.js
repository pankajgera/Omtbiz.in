import * as types from './mutation-types'
import Ls from '@/services/ls'

export default {
  [types.BOOTSTRAP_COMPANIES] (state, companies) {
    state.companies = companies
    if (0 === state.companies.length) {
      store.dispatch('auth/logout', true)
    } else {
      state.selectedCompany = companies[0]
    }
  },
  [types.SET_SELECTED_COMPANY] (state, company) {
    Ls.set('selectedCompany', company.id)
    state.selectedCompany = company
  }
}
