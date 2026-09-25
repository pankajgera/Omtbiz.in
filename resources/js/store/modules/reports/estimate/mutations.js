import * as types from './mutation-types'

export default {
  [types.SET_ESTIMATES_DRAFT] (state, data) {
    state.estimates_draft = data.estimates_draft
  },

  [types.SET_ESTIMATES_SENT] (state, data) {
    state.estimates_sent = data.estimates_sent
  }
}
