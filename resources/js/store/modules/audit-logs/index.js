import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  auditLogs: [],
  totalAuditLogs: 0
}

export default {
  namespaced: true,
  state: initialState,
  getters,
  actions,
  mutations
}
