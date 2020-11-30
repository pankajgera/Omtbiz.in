import * as types from './mutation-types'

export const fetchVouchers = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/vouchers`, {params}).then((response) => {
      commit(types.BOOTSTRAP_VOUCHERS, response.data.vouchers.data)
      commit(types.SET_TOTAL_VOUCHERS, response.data.vouchers.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchVoucher = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/vouchers/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addVoucher = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/vouchers', data).then((response) => {
      commit(types.ADD_VOUCHER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteVoucher = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/vouchers/${id}`).then((response) => {
      commit(types.DELETE_VOUCHER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleVouchers = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/vouchers/delete`, {'id': state.selectedVouchers}).then((response) => {
      commit(types.DELETE_MULTIPLE_VOUCHERS, state.selectedVouchers)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllVouchers = ({ commit, dispatch, state }) => {
  if (state.selectedVouchers.length === state.voucher.length) {
    commit(types.SET_SELECTED_VOUCHERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allVoucherIds = state.voucher.map(voucher => voucher.id)
    commit(types.SET_SELECTED_VOUCHERS, allVoucherIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectVoucher = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_VOUCHERS, data)
  if (state.selectedVouchers.length === state.voucher.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
