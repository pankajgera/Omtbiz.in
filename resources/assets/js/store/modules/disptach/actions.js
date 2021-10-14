import * as types from './mutation-types'

export const updateCurrentDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/dispatch', data).then((response) => {
            commit(types.UPDATE_CURRENT_DISPATCH, response.data.dispatch)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//new
export const dipatchedData = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/dispatch`, { params }).then((response) => {
            commit(types.BOOTSTRAP_DISPATCH, response.data.dispatch_completed.data)
            commit(types.BOOTSTRAP_TO_BE_DISPATCH, response.data.dispatch_inprogress.data)
            commit(types.SET_TOTAL_DISPATCH, response.data.dispatch_total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const editDispatch = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/dispatch/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/dispatch', data).then((response) => {
            commit(types.ADD_DISPATCH, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateDispatch = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/dispatch/${data.id}/update`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_DISPATCH, response.data)
            }
            resolve(response)
        }).catch((err) => {
          console.log(err.response)
            reject(err)
        })
    })
}

export const deleteDispatch = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/dispatch/${id}`).then((response) => {
            commit(types.DELETE_DISPATCH, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//Dispatched
export const deleteMultipleDispatch = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
      window.axios.post(`/api/dispatch/delete`, { 'id': state.selectedDispatch }).then((response) => {
          commit(types.DELETE_MULTIPLE_DISPATCH, state.selectedDispatch)
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllDispatch = ({ commit, dispatch, state }) => {
  if (state.selectedDispatch.length === state.dispatch.length) {
      commit(types.SET_SELECTED_DISPATCH, [])
      commit(types.SET_SELECT_ALL_STATE, false)
  } else {
      let allDispatchIds = state.dispatch.map(cust => cust.id)
      commit(types.SET_SELECTED_DISPATCH, allDispatchIds)
      commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectDispatch = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_DISPATCH, data)
    if (state.selectedDispatch.length === state.dispatch.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedDispatch = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_DISPATCH)
}


export const moveMultipleDispatch = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
      window.axios.post(`/api/dispatch/multiple`, { 'id': state.selectedDispatch }).then((response) => {
          //commit(types.MULTIPLE_DISPATCH, state.selectedDispatch)
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}

//To be dispatch
export const deleteMultipleToBeDispatch = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
      window.axios.post(`/api/dispatch/delete`, { 'id': state.selectedToBeDispatch }).then((response) => {
          commit(types.DELETE_MULTIPLE_DISPATCH, state.selectedDispatch)
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}

export const setSelectAllToBeState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_TO_BE_SELECT_ALL_STATE, data)
}

export const selectAllToBeDispatch = ({ commit, dispatch, state }) => {
  if (state.selectedToBeDispatch.length === state.toBeDispatch.length) {
      commit(types.SET_SELECTED_TO_BE_DISPATCH, [])
      commit(types.SET_TO_BE_SELECT_ALL_STATE, false)
  } else {
      let allToBeDispatchIds = state.toBeDispatch.map(cust => cust.id)
      commit(types.SET_SELECTED_TO_BE_DISPATCH, allToBeDispatchIds)
      commit(types.SET_TO_BE_SELECT_ALL_STATE, true)
  }
}

export const selectToBeDispatch = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_TO_BE_DISPATCH, data)
  if (state.selectedToBeDispatch.length === state.dispatch.length) {
      commit(types.SET_TO_BE_SELECT_ALL_STATE, true)
  } else {
      commit(types.SET_TO_BE_SELECT_ALL_STATE, false)
  }
}

export const resetSelectedToBeDispatch = ({ commit, dispatch, state }, data) => {
  commit(types.RESET_SELECTED_DISPATCH)
}


export const moveMultipleToBeDispatch = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
      window.axios.post(`/api/dispatch/multiple`, { 'id': state.selectedToBeDispatch }).then((response) => {
          //commit(types.MULTIPLE_TO_BE_DISPATCH, state.selectedToBeDispatch)
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}
