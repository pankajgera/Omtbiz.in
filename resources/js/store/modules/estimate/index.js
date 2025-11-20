import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  estimatesDraft: [],
  estimatesSent: [],
  estimateTemplateId: 1,
  selectAllField: false,
  selectedEstimates: [],
  totalEstimatesDraft: 0,
  totalEstimatesSent: 0,
  selectedCustomer: null
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}
