import * as types from './mutation-types'

export const updateCurrentBank = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/bank', data).then((response) => {
            commit(types.UPDATE_CURRENT_BANK, response.data.bank)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchBanksReport = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
      window.axios.get(`/api/report/bank`, { params }).then((response) => {
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}

//new
export const fetchBanks = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/bank`, { params }).then((response) => {
            commit(types.BOOTSTRAP_BANKS, response.data.banks.data)
            commit(types.SET_TOTAL_BANKS, response.data.banks.total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchBank = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/bank/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addBank = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/bank', data).then((response) => {
            commit(types.ADD_BANK, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateBank = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put(`/api/bank/${data.id}`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_BANK, response.data)
            }
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteBank = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/bank/${id}`).then((response) => {
            commit(types.DELETE_BANK, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteMultipleBanks = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/bank/delete`, { 'id': state.selectedBanks }).then((response) => {
            commit(types.DELETE_MULTIPLE_BANKS, state.selectedBanks)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const selectAllBanks = ({ commit, dispatch, state }) => {
    if (state.selectedBanks.length === state.banks.length) {
        commit(types.SET_SELECTED_BANKS, [])
        commit(types.SET_SELECT_ALL_STATE, false)
    } else {
        let allBankIds = state.banks.map(cust => cust.id)
        commit(types.SET_SELECTED_BANKS, allBankIds)
        commit(types.SET_SELECT_ALL_STATE, true)
    }
}

export const selectBank = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_BANKS, data)
    if (state.selectedBank.length === state.banks.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedBank = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_BANK)
}
