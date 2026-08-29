export const pendingOrders = (state) => state.pendingOrders
export const completedOrders = (state) => state.completedOrders
export const selectAllField = (state) => state.selectAllField
export const getTemplateId = (state) => state.orderTemplateId
export const selectedOrders = (state) => state.selectedOrders
export const totalPendingOrders = (state) => state.totalPendingOrders
export const totalCompletedOrders = (state) => state.totalCompletedOrders
export const selectedCustomer = (state) => state.selectedCustomer
export const getPendingOrder = (state) => (id) => {
  let invId = parseInt(id)
  return state.pendingOrders.find(order => order.id === invId)
}
export const getCompletedOrder = (state) => (id) => {
  let invId = parseInt(id)
  return state.completedOrders.find(order => order.id === invId)
}
