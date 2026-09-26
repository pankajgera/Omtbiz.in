import * as types from './mutation-types'

export default {
    [types.RESET_CURRENT_BANK](state, bank) {
        state.currentBank = null
    },
    [types.BOOTSTRAP_CURRENT_BANK](state, bank) {
        state.currentBank = bank
    },
    [types.UPDATE_CURRENT_BANK](state, bank) {
        state.currentBank = bank
    },

    [types.BOOTSTRAP_BANKS](state, banks) {
        state.banks = banks
    },
    [types.SET_TOTAL_BANKS](state, totalBanks) {
        state.totalBanks = totalBanks
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
        if (index !== -1) state.banks.splice(index, 1)
    },
    [types.DELETE_MULTIPLE_BANKS](state, selectedBanks) {
        selectedBanks.forEach((bank) => {
            let index = state.banks.findIndex(row => row.id === bank)
            if (index !== -1) state.banks.splice(index, 1)
        })
        state.selectedBanks = []
    },
    [types.SET_SELECTED_BANKS](state, data) {
        state.selectedBanks = data
    },
    [types.RESET_SELECTED_BANK](state, data) {
        state.selectedBanks = []
        state.selectAllField = false
    },
    [types.SET_SELECT_ALL_STATE](state, data) {
        state.selectAllField = data
    }
}
