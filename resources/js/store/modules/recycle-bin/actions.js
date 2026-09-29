export const fetchRecycleBin = (context, params) => {
  return window.axios.get('/api/recycle-bin', { params })
}

export const restoreRecycleBinEntry = (context, id) => {
  return window.axios.post(`/api/recycle-bin/${id}/restore`)
}
