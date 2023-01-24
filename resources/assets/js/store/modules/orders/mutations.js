import * as types from './mutation-types'

export default {
  [types.SET_ORDERS_PENDING] (state, data) {
    state.pendingOrders = data
  },
  [types.SET_ORDERS_COMPLETED] (state, data) {
    state.completedOrders = data
  },

  [types.SET_TOTAL_ORDERS_PENDING] (state, totalPendingOrders) {
    state.totalPendingOrders = totalPendingOrders
  },
  [types.SET_TOTAL_ORDERS_COMPLETED] (state, totalCompletedOrders) {
    state.totalCompletedOrders = totalCompletedOrders
  },

  [types.ADD_ORDER_PENDING] (state, data) {
    state.pendingOrders = [...state.pendingOrders, data]
  },
  [types.ADD_ORDER_COMPLETED] (state, data) {
    state.completedOrders = [...state.completedOrders, data]
  },

  [types.DELETE_ORDER_PENDING] (state, id) {
    let index = state.pendingOrders.findIndex(order => order.id === id)
    state.pendingOrders.splice(index, 1)
  },
  [types.DELETE_ORDER_COMPLETED] (state, id) {
    let index = state.completedOrders.findIndex(order => order.id === id)
    state.completedOrders.splice(index, 1)
  },

  [types.SET_SELECTED_ORDERS] (state, data) {
    state.selectedOrders = data
  },

  [types.DELETE_MULTIPLE_ORDERS_PENDING] (state, selectedOrders) {
    selectedOrders.forEach((order) => {
      let index = state.pendingOrders.findIndex(_est => _est.id === order.id)
      state.pendingOrders.splice(index, 1)
    })
    state.selectedOrders = []
  },
  [types.DELETE_MULTIPLE_ORDERS_COMPLETED] (state, selectedOrders) {
    selectedOrders.forEach((order) => {
      let index = state.completedOrders.findIndex(_est => _est.id === order.id)
      state.completedOrders.splice(index, 1)
    })
    state.selectedOrders = []
  },

  [types.UPDATE_ORDER_PENDING] (state, data) {
    let pos = state.pendingOrders.findIndex(order => order.id === data.order.id)
    state.pendingOrders[pos] = data.order
  },
  [types.UPDATE_ORDER_COMPLETED] (state, data) {
    let pos = state.completedOrders.findIndex(order => order.id === data.order.id)
    state.completedOrders[pos] = data.order
  },

  [types.UPDATE_ORDER_STATUS_PENDING] (state, data) {
    let pos = state.pendingOrders.findIndex(order => order.id === data.id)
    if (state.pendingOrders[pos]) {
      state.pendingOrders[pos].status = data.status
    }
  },
  [types.UPDATE_ORDER_STATUS_COMPLETED] (state, data) {
    let pos = state.completedOrders.findIndex(order => order.id === data.id)
    if (state.completedOrders[pos]) {
      state.completedOrders[pos].status = data.status
    }
  },

  [types.RESET_SELECTED_ORDERS] (state, data) {
    state.selectedOrders = []
    state.selectAllField = false
  },

  [types.SET_TEMPLATE_ID] (state, templateId) {
    state.orderTemplateId = templateId
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
