import * as types from './mutation-types'
import Ls from '@/services/ls'
// import store from './index.js'

export default {
  [types.BOOTSTRAP_COMPANIES] (state, companies) {
    state.companies = companies
    if (! state.companies || 0 === state.companies.length) {
      // store.dispatch('auth/logout', true)
      Ls.remove('auth.token')
      Ls.remove('role')
      window.location.reload()
    } else {
      state.selectedCompany = companies[0]
    }
  },
  [types.SET_SELECTED_COMPANY] (state, company) {
    if (! company) {
      // $user->company can resolve to null (e.g. an account not yet
      // assigned to a company) - keep whatever BOOTSTRAP_COMPANIES already
      // defaulted state.selectedCompany to instead of crashing here.
      return
    }
    Ls.set('selectedCompany', company.id)
    state.selectedCompany = company
  }
}
