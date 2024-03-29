import * as types from './mutation-types'

export const loadEstimateData = ({ commit, dispatch, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get('/api/report/estimate').then((response) => {
      commit(types.SET_ESTIMATES_DRAFT, response.data)
      commit(types.SET_ESTIMATES_SENT, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}
