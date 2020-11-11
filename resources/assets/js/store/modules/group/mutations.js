import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_GROUPS] (state, groups) {
    state.groups = groups
  },

  [types.SET_TOTAL_GROUPS] (state, totalGroups) {
    state.totalGroups = totalGroups
  },

  [types.ADD_GROUP] (state, data) {
    state.groups.push(data.group)
  },

  [types.UPDATE_GROUP] (state, data) {
    let pos = state.groups.findIndex(group => group.id === data.group.id)

    state.groups[pos] = data.group
  },

  [types.DELETE_GROUP] (state, id) {
    let index = state.groups.findIndex(group => group.id === id)
    state.groups.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_GROUPS] (state, selectedGroups) {
    selectedGroups.forEach((group) => {
      let index = state.groups.findIndex(_group => _group.id === group.id)
      state.groups.splice(index, 1)
    })

    state.selectedGroups = []
  },

  [types.SET_SELECTED_GROUPS] (state, data) {
    state.selectedGroups = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }

}
