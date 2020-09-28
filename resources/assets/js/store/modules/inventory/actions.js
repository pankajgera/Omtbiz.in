import * as types from './mutation-types'

export const updateCurrentInventory = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/inventory', data).then((response) => {
            commit(types.UPDATE_CURRENT_INVENTORY, response.data.inventory)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//new
export const fetchAllInventory = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/inventory`, { params }).then((response) => {
            commit(types.BOOTSTRAP_INVENTORIES, response.data.inventories.data)
            commit(types.SET_TOTAL_INVENTORIES, response.data.inventories.total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchInventory = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
      console.log('id', id);
        window.axios.get(`/api/inventory/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addInventory = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/inventory', data).then((response) => {
            commit(types.ADD_INVENTORY, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateInventory = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put(`/api/inventory/${data.id}`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_INVENTORY, response.data)
            }
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteInventory = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/inventory/${id}`).then((response) => {
            commit(types.DELETE_INVENTORY, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteMultipleInventory = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/inventory/delete`, { 'id': state.selectedInventory }).then((response) => {
            commit(types.DELETE_MULTIPLE_INVENTORY, state.selectedInventory)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const selectAllInventory = ({ commit, dispatch, state }) => {
    if (state.selectedInventory.length === state.inventory.length) {
        commit(types.SET_SELECTED_INVENTORY, [])
        commit(types.SET_SELECT_ALL_STATE, false)
    } else {
        let allInventoryIds = state.inventory.map(cust => cust.id)
        commit(types.SET_SELECTED_INVENTORY, allInventoryIds)
        commit(types.SET_SELECT_ALL_STATE, true)
    }
}

export const selectInventory = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_INVENTORY, data)
    if (state.selectedInventory.length === state.inventory.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedInventory = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_INVENTORY)
}
