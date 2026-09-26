import Ls from '@/services/ls'
import * as types from './mutation-types'
import * as userTypes from '../user/mutation-types'
import * as rootTypes from '../../mutation-types'
import router from '@/router.js'

export const login = ({ commit, dispatch, state }, data) => {
    let loginData = {
        username: data.email,
        password: data.password
    }
    return new Promise((resolve, reject) => {
        axios.post('/api/auth/login', loginData).then((response) => {
            let token = response.data.access_token
            Ls.set('auth.token', token)
            Ls.set('role', response.data.role)
            Ls.set('selectedCompany', response.data.company)
            commit('user/' + userTypes.RESET_CURRENT_USER, null, { root: true })
            commit(rootTypes.UPDATE_APP_LOADING_STATUS, false, { root: true })
            commit(types.AUTH_SUCCESS, token)
            window.toastr['success']('Login Successful')
            resolve(response)
        }).catch(err => {
            commit(types.AUTH_ERROR, err.response)
            Ls.remove('auth.token')
            Ls.remove('role')
            reject(err)
        })
    })
}

export const refreshToken = ({ commit, dispatch, state }) => {
    return new Promise((resolve, reject) => {
        let data = {
            token: Ls.get('auth.token')
        }
        axios.post('/api/auth/refresh_token', data).then((response) => {
            let token = response.data.data.token
            Ls.set('auth.token', token)
            commit(types.REFRESH_SUCCESS, token)
            resolve(response)
        }).catch(err => {
            reject(err)
        })
    })
}

export const logout = ({ commit, dispatch, state }, noRequest = false) => {
    const clearSession = () => {
        commit(types.AUTH_LOGOUT)
        commit('user/' + userTypes.RESET_CURRENT_USER, null, { root: true })
        commit(rootTypes.UPDATE_APP_LOADING_STATUS, false, { root: true })
        Ls.remove('auth.token')
        Ls.remove('role')
        Ls.remove('selectedCompany')
    }
    if (noRequest) {
        clearSession()
        router.push('/login')

        return true
    }

    return new Promise((resolve, reject) => {
        axios.get('/api/auth/logout').then((response) => {
            clearSession()
            router.push('/login')
            window.toastr['success']('Logged out!', 'Success')
            resolve(response)
        }).catch(err => {
            reject(err)
            clearSession()
            router.push('/login')
        })
    })
}
