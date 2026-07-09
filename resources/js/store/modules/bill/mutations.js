import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_Bills] (state, Bills) {
    state.Bills = Bills
  },

  [types.SET_TOTAL_Bills] (state, totalBills) {
    state.totalBills = totalBills
  },

  [types.ADD_Bill] (state, data) {
    state.Bills.push(data.Bill)
  },

  [types.UPDATE_Bill] (state, data) {
    let pos = state.Bills.findIndex(Bill => Bill.id === data.Bill.id)

    state.Bills[pos] = data.Bill
  },

  [types.DELETE_Bill] (state, id) {
    let index = state.Bills.findIndex(Bill => Bill.id === id)
    state.Bills.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_Bills] (state, selectedBills) {
    selectedBills.forEach((Bill) => {
      let index = state.Bills.findIndex(_Bill => _Bill.id === Bill.id)
      state.Bills.splice(index, 1)
    })

    state.selectedBills = []
  },

  [types.SET_SELECTED_Bills] (state, data) {
    state.selectedBills = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }

}
