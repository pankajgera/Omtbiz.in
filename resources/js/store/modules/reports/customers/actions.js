import * as types from './mutation-types'

export const loadCustomersReportLink = ({ commit, dispatch, state }, url) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/reports/customers/link`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}
