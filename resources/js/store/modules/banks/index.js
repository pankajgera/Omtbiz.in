import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  currentBank: null,
  banks: [],
  totalBanks: 0,
  selectAllField: false,
  selectedBanks: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}

