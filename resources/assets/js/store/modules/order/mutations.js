import * as types from './mutation-types'

export default {
  [types.SET_ORDERS] (state, data) {
    state.orders = data
  },

  [types.SET_TOTAL_ORDERS] (state, totalOrders) {
    state.totalOrders = totalOrders
  },

  [types.ADD_ORDER] (state, data) {
    state.orders = [...state.orders, data]
  },

  [types.DELETE_ORDER] (state, id) {
    let index = state.orders.findIndex(order => order.id === id)
    state.orders.splice(index, 1)
  },

  [types.SET_SELECTED_ORDERS] (state, data) {
    state.selectedOrders = data
  },

  [types.DELETE_MULTIPLE_ORDERS] (state, selectedOrders) {
    selectedOrders.forEach((order) => {
      let index = state.orders.findIndex(_est => _est.id === order.id)
      state.orders.splice(index, 1)
    })

    state.selectedOrders = []
  },

  [types.UPDATE_ORDER] (state, data) {
    let pos = state.orders.findIndex(order => order.id === data.order.id)

    state.orders[pos] = data.order
  },

  [types.UPDATE_ORDER_STATUS] (state, data) {
    let pos = state.orders.findIndex(order => order.id === data.id)

    if (state.orders[pos]) {
      state.orders[pos].status = data.status
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
