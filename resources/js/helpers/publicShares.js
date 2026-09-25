export async function createReportShare (type, parameters) {
  const response = await window.axios.post('/api/public-shares', {
    type,
    parameters
  })

  return response.data.url
}
