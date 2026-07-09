import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_MASTERS] (state, masters) {
    state.masters = masters
  },

  [types.SET_TOTAL_MASTERS] (state, totalMasters) {
    state.totalMasters = totalMasters
  },

  [types.ADD_MASTER] (state, data) {
    state.masters.push(data.master)
  },

  [types.UPDATE_MASTER] (state, data) {
    let pos = state.masters.findIndex(master => master.id === data.master.id)

    state.masters[pos] = data.master
  },

  [types.DELETE_MASTER] (state, id) {
    let index = state.masters.findIndex(master => master.id === id)
    state.masters.splice(index, 1)
  },

  [types.DELETE_MULTIPLE_MASTERS] (state, selectedMasters) {
    selectedMasters.forEach((master) => {
      let index = state.masters.findIndex(_master => _master.id === master.id)
      state.masters.splice(index, 1)
    })

    state.selectedMasters = []
  },

  [types.SET_SELECTED_MASTERS] (state, data) {
    state.selectedMasters = data
  },

  [types.SET_SELECT_ALL_STATE] (state, data) {
    state.selectAllField = data
  }

}
