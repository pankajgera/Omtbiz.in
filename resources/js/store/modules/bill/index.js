import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  Bills: [],
  totalBills: 0,
  selectAllField: false,
  selectedBills: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}
