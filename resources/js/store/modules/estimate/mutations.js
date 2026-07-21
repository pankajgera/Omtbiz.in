import * as types from './mutation-types'

export default {
  [types.SET_ESTIMATES_DRAFT] (state, data) {
    state.estimatesDraft = data
  },

  [types.SET_ESTIMATES_SENT] (state, data) {
    state.estimatesSent = data
  },

  [types.SET_TOTAL_ESTIMATES_DRAFT] (state, totalEstimates) {
    state.totalEstimatesDraft = totalEstimates
  },

  [types.SET_TOTAL_ESTIMATES_SENT] (state, totalEstimates) {
    state.totalEstimatesSent = totalEstimates
  },

  [types.ADD_ESTIMATE_DRAFT] (state, data) {
    state.estimatesDraft = [...state.estimatesDraft, data]
  },

  [types.ADD_ESTIMATE_SENT] (state, data) {
    state.estimatesSent = [...state.estimatesSent, data]
  },

  [types.DELETE_ESTIMATE_DRAFT] (state, id) {
    let index = state.estimatesDraft.findIndex(estimate => estimate.id === id)
    state.estimatesDraft.splice(index, 1)
  },

  [types.DELETE_ESTIMATE_SENT] (state, id) {
    let index = state.estimatesSent.findIndex(estimate => estimate.id === id)
    state.estimatesSent.splice(index, 1)
  },

  [types.SET_SELECTED_ESTIMATES] (state, data) {
    state.selectedEstimates = data
  },

  [types.DELETE_MULTIPLE_ESTIMATES] (state, selectedEstimates) {
    selectedEstimates.forEach((estimate) => {
      let index = state.estimatesDraft.findIndex(_est => _est.id === estimate.id)
      state.estimatesDraft.splice(index, 1)
    })
    state.selectedEstimates = []
  },

  [types.UPDATE_ESTIMATE_DRAFT] (state, data) {
    let pos = state.estimatesDraft.findIndex(estimate => estimate.id === data.estimate.id)
    state.estimatesDraft[pos] = data.estimate
  },

  [types.UPDATE_ESTIMATE_SENT] (state, data) {
    let pos = state.estimatesSent.findIndex(estimate => estimate.id === data.estimate.id)
    state.estimatesSent[pos] = data.estimate
  },

  [types.UPDATE_ESTIMATE_STATUS] (state, data) {
    let pos1 = state.estimatesDraft.findIndex(estimate => estimate.id === data.id)
    let pos2 = state.estimatesSent.findIndex(estimate => estimate.id === data.id)

    if (state.estimatesDraft[pos1]) {
      state.estimatesDraft[pos1].status = data.status
    }
    if (state.estimatesSent[pos2]) {
      state.estimatesSent[pos2].status = data.status
    }
  },

  [types.RESET_SELECTED_ESTIMATES] (state, data) {
    state.selectedEstimates = []
    state.selectAllField = false
  },

  [types.SET_TEMPLATE_ID] (state, templateId) {
    state.estimateTemplateId = templateId
  },

  [types.SELECT_CUSTOMER] (state, data) {
    state.selectedCustomer = data
  },

  [types.RESET_SELECTED_CUSTOMER] (state, data) {
    state.selectedCustomer = null
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }
}
