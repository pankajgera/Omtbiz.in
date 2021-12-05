import * as types from './mutation-types'

export const fetchItems = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/items`, {params}).then((response) => {
      commit(types.BOOTSTRAP_ITEMS, response.data.items.data, response.data.itemsToBe.total)
      commit(types.SET_TOTAL_ITEMS, response.data.items.total)
      commit(types.SET_TOTAL_ITEMS_TO_BE, response.data.itemsToBe.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchItem = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/items/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addItem = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/items', data).then((response) => {
      commit(types.ADD_ITEM, response.data)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateItem = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/items/${data.id}`, data).then((response) => {
      commit(types.UPDATE_ITEM, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteItem = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/items/${id}`).then((response) => {
      commit(types.DELETE_ITEM, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleItems = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/items/delete`, {'id': state.selectedItems}).then((response) => {
      commit(types.DELETE_MULTIPLE_ITEMS, state.selectedItems)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}
export const setSelectAllStateToBe = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE_TO_BE, data)
}

export const selectAllItems = ({ commit, dispatch, state }) => {
  if (state.selectedItems.length === state.items.length) {
    commit(types.SET_SELECTED_ITEMS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allItemIds = state.items.map(item => item.id)
    commit(types.SET_SELECTED_ITEMS, allItemIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}
export const selectAllItemsToBe = ({ commit, dispatch, state }) => {
  if (state.selectedItemsToBe.length === state.items.length) {
    commit(types.SET_SELECTED_ITEMS_TO_BE, [])
    commit(types.SET_SELECT_ALL_STATE_TO_BE, false)
  } else {
    let allItemIdsToBe = state.items.map(item => item.id)
    commit(types.SET_SELECTED_ITEMS_TO_BE, allItemIdsToBe)
    commit(types.SET_SELECT_ALL_STATE_TO_BE, true)
  }
}

export const selectItem = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_ITEMS, data)
  if (state.selectedItems.length === state.items.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
