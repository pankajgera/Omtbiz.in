import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_AUDIT_LOGS] (state, logs) {
    state.auditLogs = logs
  },
  [types.SET_TOTAL_AUDIT_LOGS] (state, total) {
    state.totalAuditLogs = total
  }
}
