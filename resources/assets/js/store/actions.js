import * as types from './mutation-types'
import * as currencyTypes from './modules/currency/mutation-types'
import * as userTypes from './modules/user/mutation-types'
import * as companyTypes from './modules/company/mutation-types'
import * as preferencesTypes from './modules/settings/preferences/mutation-types'

export default {
  bootstrap ({ commit, dispatch, state }) {
    return new Promise((resolve, reject) => {
      window.axios.get('/api/bootstrap').then((response) => {
        commit('company/' + companyTypes.BOOTSTRAP_COMPANIES, response.data.companies)
        commit('company/' + companyTypes.SET_SELECTED_COMPANY, response.data.company)
        commit('currency/' + currencyTypes.BOOTSTRAP_CURRENCIES, response.data)
        commit('currency/' + currencyTypes.SET_DEFAULT_CURRENCY, response.data)
        commit('user/' + userTypes.BOOTSTRAP_CURRENT_USER, response.data.user)
        commit('preferences/' + preferencesTypes.SET_MOMENT_DATE_FORMAT, response.data.moment_date_format)
        commit('preferences/' + preferencesTypes.SET_LANGUAGE_FORMAT, response.data.default_language)
        commit(types.UPDATE_APP_LOADING_STATUS, true)
        resolve(response)
      }).catch((err) => {
        reject(err)
      })
    })
  }
}
