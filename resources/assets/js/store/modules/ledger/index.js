import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  ledgers: [],
  daybook: [],
  totalLedgers: 0,
  totalDaybook: 0,
  selectAllField: false,
  selectedLedgers: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}
