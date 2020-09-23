import * as types from './mutation-types'

export default {
    [types.RESET_CURRENT_INVENTORY](state, inventory) {
        state.currentInventory = null
    },
    [types.BOOTSTRAP_CURRENT_INVENTORY](state, inventory) {
        state.currentInventory = inventory
    },
    [types.UPDATE_CURRENT_INVENTORY](state, inventory) {
        state.currentInventory = inventory
    },

    [types.BOOTSTRAP_INVENTORY](state, inventory) {
        state.inventory = inventory
    },
    [types.SET_TOTAL_INVENTORY](state, totalInventory) {
        state.totalInventory = totalInventory
    },
    [types.ADD_INVENTORY](state, data) {
        state.inventory.push(data.inventory)
    },
    [types.UPDATE_INVENTORY](state, data) {
        let pos = state.inventory.findIndex(inventory => inventory.id === data.inventory.id)
        state.inventory[pos] = data.inventory
    },
    [types.DELETE_INVENTORY](state, id) {
        let index = state.inventory.findIndex(inventory => inventory.id === id)
        state.inventory.splice(index, 1)
    },
    [types.DELETE_MULTIPLE_INVENTORY](state, selectedInventory) {
        selectedInventory.forEach((inventory) => {
            let index = state.inventory.findIndex(_cust => _cust.id === inventory.id)
            state.inventory.splice(index, 1)
        })
        state.selectedInventory = []
    },
    [types.SET_SELECTED_INVENTORY](state, data) {
        state.selectedInventory = data
    },
    [types.RESET_SELECTED_INVENTORY](state, data) {
        state.selectedInventory = null
    },
    [types.SET_SELECT_ALL_STATE](state, data) {
        state.selectAllField = data
    }
}
