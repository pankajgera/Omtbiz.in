import * as types from './mutation-types'

export const updateCurrentDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/dispatch', data).then((response) => {
            commit(types.UPDATE_CURRENT_DISPATCH, response.data.dispatch)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//new
export const fetchDispatch = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/dispatch`, { params }).then((response) => {
            // commit(types.BOOTSTRAP_DISPATCH, response.data.dispatch.data)
            commit(types.SET_TOTAL_DISPATCH, response.data.dispatch_total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const editDispatch = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/dispatch/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/dispatch', data).then((response) => {
            commit(types.ADD_DISPATCH, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put(`/api/dispatch/${data.id}`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_DISPATCH, response.data)
            }
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteDispatch = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/dispatch/${id}`).then((response) => {
            commit(types.DELETE_DISPATCH, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteMultipleDispatch = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/dispatch/delete`, { 'id': state.selectedDispatch }).then((response) => {
            commit(types.DELETE_MULTIPLE_DISPATCH, state.selectedDispatch)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const selectAllDispatch = ({ commit, dispatch, state }) => {
    if (state.selectedDispatch.length === state.dispatch.length) {
        commit(types.SET_SELECTED_DISPATCH, [])
        commit(types.SET_SELECT_ALL_STATE, false)
    } else {
        let allDispatchIds = state.dispatch.map(cust => cust.id)
        commit(types.SET_SELECTED_DISPATCH, allDispatchIds)
        commit(types.SET_SELECT_ALL_STATE, true)
    }
}

export const selectDispatch = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_DISPATCH, data)
    if (state.selectedDispatch.length === state.dispatch.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedDispatch = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_DISPATCH)
}
