import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  vouchers: [],
  totalVouchers: 0,
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
