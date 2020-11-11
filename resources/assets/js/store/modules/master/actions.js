import * as types from './mutation-types'

export const fetchMasters = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/masters`, {params}).then((response) => {
      commit(types.BOOTSTRAP_MASTERS, response.data.masters.data)
      commit(types.SET_TOTAL_MASTERS, response.data.masters.total)
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
  if (state.selectedMasters.length === state.master.length) {
    commit(types.SET_SELECTED_MASTERS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allMasterIds = state.master.map(master => master.id)
    commit(types.SET_SELECTED_MASTERS, allMasterIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectMaster = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_MASTERS, data)
  if (state.selectedMasters.length === state.master.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
