import * as types from './mutation-types'

export const fetchReceipts = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/receipts`, {params}).then((response) => {
      commit(types.SET_RECEIPTS, response.data.receipts.data)
      commit(types.SET_TOTAL_RECEIPTS, response.data.receipts.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateReceipt = ({ commit, dispatch }, page) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/receipts/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchReceipt = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/receipts/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addReceipt = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/receipts', data).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectReceipt = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_RECEIPTS, data)
  if (state.selectedReceipts.length === state.receipts.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const updateReceipt = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/receipts/${data.id}`, data.editData).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteReceipt = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/receipts/${id}`).then((response) => {
      commit(types.DELETE_RECEIPT, id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleReceipts = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/receipts/delete`, {'id': state.selectedReceipts}).then((response) => {
      commit(types.DELETE_MULTIPLE_RECEIPTS, state.selectedReceipts)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const selectAllReceipts = ({ commit, dispatch, state }) => {
  if (state.selectedReceipts.length === state.receipts.length) {
    commit(types.SET_SELECTED_RECEIPTS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allReceiptIds = state.receipts.map(pay => pay.id)
    commit(types.SET_SELECTED_RECEIPTS, allReceiptIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}
