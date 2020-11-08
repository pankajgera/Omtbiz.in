import * as types from './mutation-types'

export const fetchMasters = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/master`, {params}).then((response) => {
      commit(types.BOOTSTRAP_MASTERS, response.data.master.data)
      commit(types.SET_TOTAL_MASTERS, response.data.master.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchMaster = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/master/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addMaster = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/master', data).then((response) => {
      commit(types.ADD_MASTER, response.data)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateMaster = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/master/${data.id}`, data).then((response) => {
      commit(types.UPDATE_MASTER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMaster = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/master/${id}`).then((response) => {
      commit(types.DELETE_MASTER, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleMasters = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/master/delete`, {'id': state.selectedMasters}).then((response) => {
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
