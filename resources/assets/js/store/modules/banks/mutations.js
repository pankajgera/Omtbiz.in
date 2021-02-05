import * as types from './mutation-types'

export default {
    [types.RESET_CURRENT_BANK](state, bank) {
        state.currentNote = null
    },
    [types.BOOTSTRAP_CURRENT_BANK](state, bank) {
        state.currentNote = bank
    },
    [types.UPDATE_CURRENT_BANK](state, bank) {
        state.currentNote = bank
    },

    [types.BOOTSTRAP_BANKS](state, banks) {
        state.banks = banks
    },
    [types.SET_TOTAL_BANKS](state, totalNotes) {
        state.totalNotes = totalNotes
    },
    [types.ADD_BANK](state, data) {
        state.banks.push(data.bank)
    },
    [types.UPDATE_BANK](state, data) {
        let pos = state.banks.findIndex(bank => bank.id === data.bank.id)
        state.banks[pos] = data.bank
    },
    [types.DELETE_BANK](state, id) {
        let index = state.banks.findIndex(bank => bank.id === id)
        state.banks.splice(index, 1)
    },
    [types.DELETE_MULTIPLE_BANKS](state, selectedNotes) {
        selectedNotes.forEach((bank) => {
            let index = state.banks.findIndex(_cust => _cust.id === bank.id)
            state.banks.splice(index, 1)
        })
        state.selectedNotes = []
    },
    [types.SET_SELECTED_BANKS](state, data) {
        state.selectedNotes = data
    },
    [types.RESET_SELECTED_BANK](state, data) {
        state.selectedNote = null
    },
    [types.SET_SELECT_ALL_STATE](state, data) {
        state.selectAllField = data
    }
}
