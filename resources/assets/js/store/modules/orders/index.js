import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  orders: [],
  orderTemplateId: 1,
  selectAllField: false,
  selectedOrders: [],
  totalOrders: 0,
  selectedCustomer: null
}

export default {
  namespaced: true,
  state: initialState,
  getters: getters,
  actions: actions,
  mutations: mutations
}
