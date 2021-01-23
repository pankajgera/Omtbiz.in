import * as types from './mutation-types'

export default {
  [types.SET_RECEIPTS] (state, receipts) {
    state.receipts = receipts
  },

  [types.SET_TOTAL_RECEIPTS] (state, totalReceipts) {
    state.totalReceipts = totalReceipts
  },

  [types.ADD_RECEIPT] (state, data) {
    state.receipts.push(data)
  },

  [types.DELETE_RECEIPT] (state, id) {
    let index = state.receipts.findIndex(receipt => receipt.id === id)
    state.receipts.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_RECEIPTS] (state, selectedReceipts) {
    selectedReceipts.forEach((receipt) => {
      let index = state.receipts.findIndex(_inv => _inv.id === receipt.id)
      state.receipts.splice(index, 1)
    })

    state.selectedReceipts = []
  },

  [types.SET_SELECTED_RECEIPTS] (state, data) {
    state.selectedReceipts = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }
}
