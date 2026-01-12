import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_LEDGERS] (state, ledgers) {
    state.ledgers = ledgers
  },

  [types.SET_TOTAL_LEDGERS] (state, totalLedgers) {
    state.totalLedgers = totalLedgers
  },

  [types.ADD_LEDGER] (state, data) {
    state.ledgers.push(data.ledger)
  },

  [types.UPDATE_LEDGER] (state, data) {
    let pos = state.ledgers.findIndex(ledger => ledger.id === data.ledger.id)

    state.ledgers[pos] = data.ledger
  },

  [types.DELETE_LEDGER] (state, id) {
    let index = state.ledgers.findIndex(ledger => ledger.id === id)
    state.ledgers.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_LEDGERS] (state, selectedLedgers) {
    selectedLedgers.forEach((ledger) => {
      let index = state.ledgers.findIndex(_ledger => _ledger.id === ledger.id)
      state.ledgers.splice(index, 1)
    })

    state.selectedLedgers = []
  },

  [types.SET_SELECTED_LEDGERS] (state, data) {
    state.selectedLedgers = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }

}
