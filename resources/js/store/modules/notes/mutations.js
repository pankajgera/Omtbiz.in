import * as types from './mutation-types'

export default {
    [types.RESET_CURRENT_NOTE](state, note) {
        state.currentNote = null
    },
    [types.BOOTSTRAP_CURRENT_NOTE](state, note) {
        state.currentNote = note
    },
    [types.UPDATE_CURRENT_NOTE](state, note) {
        state.currentNote = note
    },

    [types.BOOTSTRAP_NOTES](state, notes) {
        state.notes = notes
    },
    [types.SET_TOTAL_NOTES](state, totalNotes) {
        state.totalNotes = totalNotes
    },
    [types.ADD_NOTE](state, data) {
        state.notes.push(data.note)
    },
    [types.UPDATE_NOTE](state, data) {
        let pos = state.notes.findIndex(note => note.id === data.note.id)
        state.notes[pos] = data.note
    },
    [types.DELETE_NOTE](state, id) {
        let index = state.notes.findIndex(note => note.id === id)
        state.notes.splice(index, 1)
    },
    [types.DELETE_MULTIPLE_NOTES](state, selectedNotes) {
        selectedNotes.forEach((note) => {
            let index = state.notes.findIndex(_cust => _cust.id === note.id)
            state.notes.splice(index, 1)
        })
        state.selectedNotes = []
    },
    [types.SET_SELECTED_NOTES](state, data) {
        state.selectedNotes = data
    },
    [types.RESET_SELECTED_NOTE](state, data) {
        state.selectedNote = null
    },
    [types.SET_SELECT_ALL_STATE](state, data) {
        state.selectAllField = data
    }
}
