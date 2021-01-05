import * as types from './mutation-types'

export const fetchStates = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/states`).then((response) => {
      commit(types.BOOTSTRAP_STATES, response.data.states.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}
