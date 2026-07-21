import mutations from './mutations'
import * as actions from './actions'
import * as getters from './getters'

const initialState = {
  items: [],
  itemsTobe: [],
  totalItems: 0,
  totalItemsToBe: 0,
  selectAllField: false,
  selectAllFieldToBe: false,
  selectedItems: [],
  selectedItemsToBe: []
}

export default {
  namespaced: true,

  state: initialState,

  getters: getters,

  actions: actions,

  mutations: mutations
}
