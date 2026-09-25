import * as types from './mutation-types'

export const fetchMasters = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/masters`, {params}).then((response) => {
      // /api/masters answers in two shapes: a paginator normally, but a bare
      // collection when called with limit=false - which is what the party
      // dropdowns on the voucher and ledger forms do. Only the paginated shape
      // belongs in the store.
      //
      // Committing unconditionally meant those dropdown calls wrote
      // `undefined` (a plain array has no `.data`) over state.masters, so the
      // masters list page then blew up on `masters.length` and rendered blank -
      // reachable by opening a voucher or ledger form and navigating to it.
      // Both callers read the response directly, so skipping the commit costs
      // them nothing.
      let masters = response.data.masters
      if (masters && Array.isArray(masters.data)) {
        commit(types.BOOTSTRAP_MASTERS, masters.data)
        commit(types.SET_TOTAL_MASTERS, masters.total)
      }
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchMaster = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/masters/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchGroups = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/masters/groups`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addMaster = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/masters', data).then((response) => {
      commit(types.ADD_MASTER, response.data)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateMaster = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/masters/${data.id}`, data).then((response) => {
      commit(types.UPDATE_MASTER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMaster = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/masters/${id}`).then((response) => {
      commit(types.DELETE_MASTER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleMasters = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/masters/delete`, {'id': state.selectedMasters}).then((response) => {
      commit(types.DELETE_MULTIPLE_MASTERS, state.selectedMasters)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllMasters = ({ commit, dispatch, state }) => {
  if (state.selectedMasters.length === state.masters.length) {
    commit(types.SET_SELECTED_MASTERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allMasterIds = state.masters.map(master => master.id)
    commit(types.SET_SELECTED_MASTERS, allMasterIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectMaster = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_MASTERS, data)
  if (state.selectedMasters.length === state.masters.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}

export const checkMasterName = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
      window.axios.post(`/api/master/check-name`, data).then((response) => {
          resolve(response)
      }).catch((err) => {
          reject(err)
      })
  })
}
