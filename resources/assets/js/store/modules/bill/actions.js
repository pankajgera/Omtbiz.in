import * as types from './mutation-types'

export const fetchBills = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/bills`, {params}).then((response) => {
      commit(types.BOOTSTRAP_Bills, response.data.Bills.data)
      commit(types.SET_TOTAL_Bills, response.data.Bills.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchBill = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/bills/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addBill = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/bills', data).then((response) => {
      commit(types.ADD_Bill, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateBill = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/bills/${data.id}`, data).then((response) => {
      commit(types.UPDATE_Bill, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteBill = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/bills/${id}`).then((response) => {
      commit(types.DELETE_Bill, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleBills = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/bills/delete`, {'id': state.selectedBills}).then((response) => {
      commit(types.DELETE_MULTIPLE_Bills, state.selectedBills)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllBills = ({ commit, dispatch, state }) => {
  if (state.selectedBills.length === state.Bills.length) {
    commit(types.SET_SELECTED_Bills, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allBillIds = state.Bills.map(Bill => Bill.id)
    commit(types.SET_SELECTED_Bills, allBillIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectBill = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_Bills, data)
  if (state.selectedBills.length === state.Bills.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
