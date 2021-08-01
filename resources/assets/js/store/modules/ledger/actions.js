import * as types from './mutation-types'

export const fetchLedgers = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/ledgers`, {params}).then((response) => {
      commit(types.BOOTSTRAP_LEDGERS, response.data.ledgers.data)
      commit(types.SET_TOTAL_LEDGERS, response.data.ledgers.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchDaybook = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/vouchers/daybook`, {params}).then((response) => {
      commit(types.BOOTSTRAP_DAYBOOK, response.data.daybook.data)
      commit(types.SET_TOTAL_DAYBOOK, response.data.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchLedger = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/ledgers/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchLedgerDisplay = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/ledgers/${id}/display`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchVoucherBook = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/vouchers/${id}/book`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addLedger = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/ledgers', data).then((response) => {
      commit(types.ADD_LEDGER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateLedger = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/ledgers/${data.id}`, data).then((response) => {
      commit(types.UPDATE_LEDGER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteLedger = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/ledgers/${id}`).then((response) => {
      commit(types.DELETE_LEDGER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleLedgers = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/ledgers/delete`, {'id': state.selectedLedgers}).then((response) => {
      commit(types.DELETE_MULTIPLE_LEDGERS, state.selectedLedgers)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllLedgers = ({ commit, dispatch, state }) => {
  if (state.selectedLedgers.length === state.ledger.length) {
    commit(types.SET_SELECTED_LEDGERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allLedgerIds = state.ledger.map(ledger => ledger.id)
    commit(types.SET_SELECTED_LEDGERS, allLedgerIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectLedger = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_LEDGERS, data)
  if (state.selectedLedgers.length === state.ledger.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
