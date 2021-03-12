import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  currentDispatch: null,
  dispatch: [],
  totalDispatch: 0,
  selectAllField: false,
  selectedDispatch: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}

