import * as types from './mutation-types'

export default {
  [types.BOOTSTRAP_STATES] (state, states) {
    state.states = states
  },
}
