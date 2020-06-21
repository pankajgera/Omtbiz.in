import * as types from './mutation-types'

export const loadData = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/settings/add-user`).then((response) => {
            commit(types.SET_USER, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const editUser = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put('/api/settings/add-user', data).then((response) => {
            commit(types.UPDATE_USER, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const uploadOnboardAvatar = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/admin/add-user/upload-avatar`, data).then((response) => {
            commit(types.UPDATE_USER, response.data.user)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const uploadAvatar = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/settings/add-user/upload-avatar', data).then((response) => {
            commit(types.UPDATE_USER, response.data.user)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}