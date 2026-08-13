import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  currentDispatch: null,
  dispatch: [],
  toBeDispatch: [],
  totalDispatch: 0,
  selectAllField: false,
  selectAllToBeField: false,
  selectedDispatch: [],
  selectedToBeDispatch: [],
  dashboardCounts: {
    pending_count: 0,
    dispatched_count: 0,
    total_count: 0,
  },
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}

