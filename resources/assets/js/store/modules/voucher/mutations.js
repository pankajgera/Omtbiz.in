import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_VOUCHERS] (state, vouchers) {
    state.vouchers = vouchers
  },

  [types.SET_TOTAL_VOUCHERS] (state, totalVouchers) {
    state.totalVouchers = totalVouchers
  },

  [types.ADD_VOUCHER] (state, data) {
    state.vouchers.push(data.voucher)
  },

  [types.UPDATE_VOUCHER] (state, data) {
    let pos = state.vouchers.findIndex(voucher => voucher.id === data.voucher.id)

    state.vouchers[pos] = data.voucher
  },

  [types.DELETE_VOUCHER] (state, id) {
    let index = state.vouchers.findIndex(voucher => voucher.id === id)
    state.vouchers.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_VOUCHERS] (state, selectedVouchers) {
    selectedVouchers.forEach((voucher) => {
      let index = state.vouchers.findIndex(_voucher => _voucher.id === voucher.id)
      state.vouchers.splice(index, 1)
    })

    state.selectedVouchers = []
  },

  [types.SET_SELECTED_VOUCHERS] (state, data) {
    state.selectedVouchers = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }

}
