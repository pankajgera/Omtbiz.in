import * as types from './mutation-types'

export const updateCurrentNote = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        axios.post('/api/hq/notes', data).then((response) => {
            commit(types.UPDATE_CURRENT_NOTE, response.data.note)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

//new
export const fetchNotes = ({ commit, dispatch, state }, params) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/notes`, { params }).then((response) => {
            commit(types.BOOTSTRAP_NOTES, response.data.notes.data)
            commit(types.SET_TOTAL_NOTES, response.data.notes.total)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const fetchNote = ({ commit, dispatch }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.get(`/api/notes/${id}/edit`).then((response) => {
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const addNote = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.post('/api/notes', data).then((response) => {
            commit(types.ADD_NOTE, response.data)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const updateNote = ({ commit, dispatch, state }, data) => {
    return new Promise((resolve, reject) => {
        window.axios.put(`/api/notes/${data.id}`, data).then((response) => {
            if (response.data.success) {
                commit(types.UPDATE_NOTE, response.data)
            }
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteNote = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.delete(`/api/notes/${id}`).then((response) => {
            commit(types.DELETE_NOTE, id)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const deleteMultipleNotes = ({ commit, dispatch, state }, id) => {
    return new Promise((resolve, reject) => {
        window.axios.post(`/api/notes/delete`, { 'id': state.selectedNotes }).then((response) => {
            commit(types.DELETE_MULTIPLE_NOTES, state.selectedNotes)
            resolve(response)
        }).catch((err) => {
            reject(err)
        })
    })
}

export const setSelectAllState = ({ commit, dispatch, state }, data) => {
  commit(types.SET_SELECT_ALL_STATE, data)
}

export const selectAllNotes = ({ commit, dispatch, state }) => {
    if (state.selectedNotes.length === state.notes.length) {
        commit(types.SET_SELECTED_NOTES, [])
        commit(types.SET_SELECT_ALL_STATE, false)
    } else {
        let allNoteIds = state.notes.map(cust => cust.id)
        commit(types.SET_SELECTED_NOTES, allNoteIds)
        commit(types.SET_SELECT_ALL_STATE, true)
    }
}

export const selectNote = ({ commit, dispatch, state }, data) => {
    commit(types.SET_SELECTED_NOTES, data)
    if (state.selectedNote.length === state.notes.length) {
        commit(types.SET_SELECT_ALL_STATE, true)
    } else {
        commit(types.SET_SELECT_ALL_STATE, false)
    }
}

export const resetSelectedNote = ({ commit, dispatch, state }, data) => {
    commit(types.RESET_SELECTED_NOTE)
}
