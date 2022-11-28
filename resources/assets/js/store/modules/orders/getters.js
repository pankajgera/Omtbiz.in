export const orders = (state) => state.orders
export const selectAllField = (state) => state.selectAllField
export const getTemplateId = (state) => state.orderTemplateId
export const selectedOrders = (state) => state.selectedOrders
export const totalOrders = (state) => state.totalOrders
export const selectedCustomer = (state) => state.selectedCustomer
export const getOrder = (state) => (id) => {
  let invId = parseInt(id)
  return state.orders.find(order => order.id === invId)
}
