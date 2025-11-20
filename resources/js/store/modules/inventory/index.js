import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  currentInventory: null,
  inventory: [],
  inventories: [],
  totalInventories: 0,
  selectAllField: false,
  selectedInventory: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}

