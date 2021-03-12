import * as types from './mutation-types'

export default {
    [types.RESET_CURRENT_DISPATCH](state, dispatch) {
        state.currentDispatch = null
    },
    [types.BOOTSTRAP_CURRENT_DISPATCH](state, dispatch) {
        state.currentDispatch = dispatch
    },
    [types.UPDATE_CURRENT_DISPATCH](state, dispatch) {
        state.currentDispatch = dispatch
    },

    [types.BOOTSTRAP_DISPATCH](state, dispatch) {
        state.dispatch = dispatch
    },
    [types.SET_TOTAL_DISPATCH](state, totalDispatch) {
        state.totalDispatch = totalDispatch
    },
    [types.ADD_DISPATCH](state, data) {
        state.dispatch.push(data.dispatch)
    },
    [types.UPDATE_DISPATCH](state, data) {
        let pos = state.dispatch.findIndex(dispatch => dispatch.id === data.dispatch.id)
        state.dispatch[pos] = data.dispatch
    },
    [types.DELETE_DISPATCH](state, id) {
        let index = state.dispatch.findIndex(dispatch => dispatch.id === id)
        state.dispatch.splice(index, 1)
    },
    [types.DELETE_MULTIPLE_DISPATCH](state, selectedDispatch) {
        selectedDispatch.forEach((dispatch) => {
            let index = state.dispatch.findIndex(_cust => _cust.id === dispatch.id)
            state.dispatch.splice(index, 1)
        })
        state.selectedDispatch = []
    },
    [types.SET_SELECTED_DISPATCH](state, data) {
        state.selectedDispatch = data
    },
    [types.RESET_SELECTED_DISPATCH](state, data) {
        state.selectedDispatch = null
    },
    [types.SET_SELECT_ALL_STATE](state, data) {
        state.selectAllField = data
    }
}
