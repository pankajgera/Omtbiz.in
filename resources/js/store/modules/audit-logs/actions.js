import * as types from './mutation-types'

export const fetchAuditLogs = ({ commit }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get('/api/audit-logs', { params }).then((response) => {
      commit(types.BOOTSTRAP_AUDIT_LOGS, response.data.audit_logs.data)
      commit(types.SET_TOTAL_AUDIT_LOGS, response.data.audit_logs.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchAuditLog = ({ commit }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/audit-logs/${id}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}
