import * as actions from './actions'

const initialState = {
    credits: []
}


export default {
    namespaced: true,

    state: initialState,

    actions: actions
}