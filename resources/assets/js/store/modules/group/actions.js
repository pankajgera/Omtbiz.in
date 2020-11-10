import * as types from './mutation-types'

export const fetchGroups = ({ commit, dispatch, state }, params) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/groups`, {params}).then((response) => {
      commit(types.BOOTSTRAP_GROUPS, response.data.groups.data)
      commit(types.SET_TOTAL_GROUPS, response.data.groups.total)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchGroup = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/groups/${id}/edit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const addGroup = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.post('/api/groups', data).then((response) => {
      commit(types.ADD_GROUP, response.data)

      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const updateGroup = ({ commit, dispatch, state }, data) => {
  return new Promise((resolve, reject) => {
    window.axios.put(`/api/groups/${data.id}`, data).then((response) => {
      commit(types.UPDATE_GROUP, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteGroup = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.delete(`/api/groups/${id}`).then((response) => {
      commit(types.DELETE_GROUP, response.data)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const deleteMultipleGroups = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.post(`/api/groups/delete`, {'id': state.selectedGroups}).then((response) => {
      commit(types.DELETE_MULTIPLE_GROUPS, state.selectedGroups)
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllGroups = ({ commit, dispatch, state }) => {
  if (state.selectedGroups.length === state.group.length) {
    commit(types.SET_SELECTED_GROUPS, [])
    commit(types.SET_SELECT_ALL_STATE, false)
  } else {
    let allGroupIds = state.group.map(group => group.id)
    commit(types.SET_SELECTED_GROUPS, allGroupIds)
    commit(types.SET_SELECT_ALL_STATE, true)
  }
}

export const selectGroup = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECTED_GROUPS, data)
  if (state.selectedGroups.length === state.group.length) {
    commit(types.SET_SELECT_ALL_STATE, true)
  } else {
    commit(types.SET_SELECT_ALL_STATE, false)
  }
}
