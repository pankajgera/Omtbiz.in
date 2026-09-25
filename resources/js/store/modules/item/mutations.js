import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_ITEMS] (state, itemsArray) {
    state.items = itemsArray[0]
    state.itemsToBe = itemsArray[1]
  },

  [types.SET_TOTAL_ITEMS] (state, totalItems) {
    state.totalItems = totalItems
  },
  [types.SET_TOTAL_ITEMS_TO_BE] (state, totalItemsToBe) {
    state.totalItemsToBe = totalItemsToBe
  },

  [types.ADD_ITEM] (state, data) {
    state.items.push(data.item)
  },

  [types.UPDATE_ITEM] (state, data) {
    let pos = state.items.findIndex(item => item.id === data.item.id)

    state.items[pos] = data.item
  },

  [types.DELETE_ITEM] (state, id) {
    let index = state.items.findIndex(item => item.id === id)
    state.items.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_ITEMS] (state, selectedItems) {
    selectedItems.forEach((item) => {
      let index = state.items.findIndex(_item => _item.id === item.id)
      state.items.splice(index, 1)
    })
    state.selectedItems = []
  },
  [types.DELETE_MULTIPLE_ITEMS_TO_BE] (state, selectedItemsToBe) {
    selectedItemsToBe.forEach((item) => {
      let index = state.items.findIndex(_item => _item.id === item.id)
      state.items.splice(index, 1)
    })
    state.selectedItemsToBe = []
  },

  [types.SET_SELECTED_ITEMS] (state, data) {
    state.selectedItems = data
  },
  [types.SET_SELECTED_ITEMS_TO_BE] (state, data) {
    state.selectedItemsToBe = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  },
  [types.SET_SELECT_ALL_STATE_TO_BE] (state, data) {
    state.selectAllFieldToBe = data
  }

}
