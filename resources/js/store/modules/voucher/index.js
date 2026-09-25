import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  vouchers: [],
  daybook: [],
  totalVouchers: 0,
  totalDaybook: 0,
  selectAllField: false,
  selectedVouchers: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}
