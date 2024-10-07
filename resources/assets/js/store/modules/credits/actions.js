

export const fetchCredits = ({ commit, dispatch, state }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/credits/${id}/credit`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}

export const fetchLedgersReport = ({ commit, dispatch }, id) => {
  return new Promise((resolve, reject) => {
    window.axios.get(`/api/reports/ledger`).then((response) => {
      resolve(response)
    }).catch((err) => {
      reject(err)
    })
  })
}


export const sendReportOnWhatsApp = ({ commit, dispatch, state}, data) => {
  let processData = qs.stringify({
      "token": "kaonxaoeurktcgsy",
      "nocache": true,
      "to": data.number,
      "filename": data.fileName + '.pdf',
      "document": data.filePath,
      "caption": data.fileName
  });
  return new Promise((resolve, reject) => {
    window.axios.post('https://api.ultramsg.com/instance66542/messages/document', processData)
    .then((response) => {
      if (response.status === 200 && !response.data?.error?.length) {
        window.swal({
          title: 'Success!',
          text: 'Message sent on Whatsapp',
          icon: '/assets/icon/envelope-solid.svg',
          dangerMode: false
        });
        resolve(response)
      } else {
        window.swal({
          title: 'Error!',
          text: 'Error while sending message on whatsapp',
          icon: '/assets/icon/envelope-solid.svg',
          dangerMode: true
        });
      }
    }).catch((err) => {
      reject(err)
    })
  })
}
