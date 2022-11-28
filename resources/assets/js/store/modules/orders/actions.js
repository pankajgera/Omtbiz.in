import * as types from './mutation-types'

export const fetchOrders = ({ commit, orders, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders`, {params}).then((response) => {
      commit(types.SET_ORDERS, response.data.orders.data)
      commit(types.SET_TOTAL_ORDERS, response.data.orderTotalCount)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const getRecord = ({ commit, orders, state }, record) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders/records?record=${record}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateOrder = ({ commit, orders, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchReferenceNumber = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/invoices/reference`, data).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchOrder = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders/${id}/edit`).then((response) => {
      commit(types.SET_TEMPLATE_ID, response.data.order.order_template_id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchViewOrder = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders/${id}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const sendEmail = ({ commit, orders, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/orders/send`, data).then((response) => {
      if (response.data.success) {
        commit(types.UPDATE_ORDER_STATUS, {id: data.id, status: 'SENT'})
        commit('dashboard/' + types.UPDATE_ORDER_STATUS, { id: data.id, status: 'SENT' }, { root: true })
      }
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addOrder = ({ commit, orders, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/orders', data).then((response) => {
      commit(types.ADD_ORDER, response.data.order)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteOrder = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/orders/${id}`).then((response) => {
      commit(types.DELETE_ORDER, id)
      commit('dashboard/' + types.DELETE_ORDER, id, { root: true })
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleOrders = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/orders/delete`, {'id': state.selectedOrders}).then((response) => {
      commit(types.DELETE_MULTIPLE_ORDERS, state.selectedOrders)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateOrder = ({ commit, orders, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/orders/${data.id}`, data).then((response) => {
      commit(types.UPDATE_ORDER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const convertToOrder = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/orders/${id}/convert-to-order`).then((response) => {
      // commit(types.UPDATE_ORDER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const searchOrder = ({ commit, orders, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/orders?${data}`).then((response) => {
      // commit(types.UPDATE_ORDER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const selectOrder = ({ commit, orders, state }, data) => {
  commit(types.SET_SELECTED_ORDERS, data)
  if (state.selectedOrders.length === state.orders.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const setSelectAllState = ({ commit, orders, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllOrders = ({ commit, orders, state }) => {
  if (state.selectedOrders.length === state.orders.length) {
    commit(types.SET_SELECTED_ORDERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allOrderIds = state.orders.map(estimt => estimt.id)
    commit(types.SET_SELECTED_ORDERS, allOrderIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const resetSelectedOrders = ({ commit, orders, state }) => {
  commit(types.RESET_SELECTED_ORDERS)
}

export const setCustomer = ({ commit, orders, state }, data) => {
  commit(types.RESET_CUSTOMER)
  commit(types.SET_CUSTOMER, data)
}

export const setItem = ({ commit, orders, state }, data) => {
  commit(types.RESET_ITEM)
  commit(types.SET_ITEM, data)
}

export const resetCustomer = ({ commit, orders, state }) => {
  commit(types.RESET_CUSTOMER)
}

export const resetItem = ({ commit, orders, state }) => {
  commit(types.RESET_ITEM)
}

export const setTemplate = ({ commit, orders, state }, data) => {
  return new Promise((resolve, reject) => {
    commit(types.SET_TEMPLATE_ID, data)
    resolve({})
  })
}

export const selectCustomer = ({ commit, orders, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/customers/${id}`).then((response) => {
      commit(types.RESET_SELECTED_CUSTOMER)
      commit(types.SELECT_CUSTOMER, response.data.customer)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const resetSelectedCustomer = ({ commit, orders, state }, data) => {
  commit(types.RESET_SELECTED_CUSTOMER)
}
