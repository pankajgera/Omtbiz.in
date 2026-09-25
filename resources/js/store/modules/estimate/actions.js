import * as types from './mutation-types'

export const fetchEstimates = ({ commit, estimates, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates`, {params}).then((response) => {
      // The endpoint always returns both buckets, but it also applies the
      // request's `status` filter to each of them — so a DRAFT request comes
      // back with an empty `estimates_sent` and vice versa. The index page runs
      // both tables at once, so committing both buckets every time lets each
      // table wipe the other's list and leaves "select all" with nothing to
      // select. Only store the bucket this request actually asked for.
      const status = params && params.status

      if (status !== 'SENT') {
        commit(types.SET_ESTIMATES_DRAFT, response.data.estimates_draft.data)
        commit(types.SET_TOTAL_ESTIMATES_DRAFT, response.data.draft_count)
      }

      if (status !== 'DRAFT') {
        commit(types.SET_ESTIMATES_SENT, response.data.estimates_sent.data)
        commit(types.SET_TOTAL_ESTIMATES_SENT, response.data.sent_count)
      }

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const getRecord = ({ commit, estimates, state }, record) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/records?record=${record}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchCreateEstimate = ({ commit, estimates, state }) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/create`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchReferenceNumber = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/reference`, data).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchEstimate = ({ commit, estimates, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/${id}/edit`).then((response) => {
      commit(types.SET_TEMPLATE_ID, response.data.estimate.estimate_template_id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchViewEstimate = ({ commit, estimates, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates/${id}`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addEstimate = ({ commit, estimates, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/estimates', data).then((response) => {
      //new estimate is only added to draftw
      commit(types.ADD_ESTIMATE_DRAFT, response.data.estimate)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteEstimate = ({ commit, estimates, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/estimates/${id}`).then((response) => {
      commit(types.DELETE_ESTIMATE_DRAFT, id)
      commit(types.DELETE_ESTIMATE_SENT, id)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleEstimates = ({ commit, estimates, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/delete`, {'id': state.selectedEstimates}).then((response) => {
      commit(types.DELETE_MULTIPLE_ESTIMATES, state.selectedEstimates)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateEstimate = ({ commit, estimates, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/estimates/${data.id}`, data).then((response) => {
      commit(types.UPDATE_ESTIMATE_DRAFT, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const convertToEstimate = ({ commit, estimates, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/estimates/${id}/convert-to-estimate`).then((response) => {
      // commit(types.UPDATE_ESTIMATE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const searchEstimate = ({ commit, estimates, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/estimates?${data}`).then((response) => {
      // commit(types.UPDATE_ESTIMATE, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

// Both tables on the index page feed one shared selection, so "all" means
// every row currently listed across the pending and completed tables.
const listedEstimateIds = (state) => [
  ...state.estimatesDraft,
  ...state.estimatesSent
].map(estimate => estimate.id)

export const selectEstimate = ({ commit, estimates, state }, data) => {
  commit(types.SET_SELECTED_ESTIMATES, data)
  const listed = listedEstimateIds(state)
  if (listed.length && state.selectedEstimates.length === listed.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const setSelectAllState = ({ commit, estimates, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllEstimates = ({ commit, estimates, state }) => {
  let allEstimateIds = listedEstimateIds(state)

  if (state.selectedEstimates.length === allEstimateIds.length) {
    commit(types.SET_SELECTED_ESTIMATES, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    commit(types.SET_SELECTED_ESTIMATES, allEstimateIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const resetSelectedEstimates = ({ commit, estimates, state }) => {
  commit(types.RESET_SELECTED_ESTIMATES)
}

export const setCustomer = ({ commit, estimates, state }, data) => {
  commit(types.RESET_CUSTOMER)
  commit(types.SET_CUSTOMER, data)
}

export const setItem = ({ commit, estimates, state }, data) => {
  commit(types.RESET_ITEM)
  commit(types.SET_ITEM, data)
}

export const resetCustomer = ({ commit, estimates, state }) => {
  commit(types.RESET_CUSTOMER)
}

export const resetItem = ({ commit, estimates, state }) => {
  commit(types.RESET_ITEM)
}

export const setTemplate = ({ commit, estimates, state }, data) => {
  return new Promise((resolve, reject) => {
    commit(types.SET_TEMPLATE_ID, data)
    resolve({})
  })
}

export const selectCustomer = ({ commit, estimates, state }, id) => {
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

export const resetSelectedCustomer = ({ commit, estimates, state }, data) => {
  commit(types.RESET_SELECTED_CUSTOMER)
}
