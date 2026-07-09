import * as types from './mutation-types'

export const updateCurrentUser = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/users', data).then((response) => {
            commit(types.UPDATE_CURRENT_USER, response.data.user)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//new
export const fetchUsers = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/users`, { params }).then((response) => {
            commit(types.BOOTSTRAP_USERS, response.data.users.data)
            commit(types.SET_TOTAL_USERS, response.data.users.total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchUser = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/users/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchRolesAndCompanies = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/users/fetch-roles-and-companies`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addUser = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/users', data).then((response) => {
            commit(types.ADD_USER, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateUser = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put(`/api/users/${data.id}`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_USER, response.data)
            }
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteUser = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/users/${id}`).then((response) => {
            commit(types.DELETE_USER, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteMultipleUsers = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/users/delete`, { 'id': state.selectedUsers }).then((response) => {
            commit(types.DELETE_MULTIPLE_USERS, state.selectedUsers)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllUsers = ({ commit, dispatch, state }) => {
    if (state.selectedUsers.length === state.users.length) {
        commit(types.SET_SELECTED_USERS, [])
        commit(types.SET_SELECT_ALL_STATE, false)
    } else {
        let allUserIds = state.users.map(cust => cust.id)
        commit(types.SET_SELECTED_USERS, allUserIds)
        commit(types.SET_SELECT_ALL_STATE, true)
    }
}

export const selectUser = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_USERS, data)
    if (state.selectedUser.length === state.users.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedUser = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_USER)
}