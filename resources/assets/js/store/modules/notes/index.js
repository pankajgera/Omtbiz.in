import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  currentNote: null,
  roles: [],
  permissions: [],
  stations: [],
  notes: [],
  totalNotes: 0,
  selectAllField: false,
  selectedNotes: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}

