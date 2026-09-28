// Print the saved HTML report in this tab without replacing the invoice form.
// Resolve only after the browser reports that printing/preview has finished.
export function printInvoiceReport (url) {
  return new Promise((resolve, reject) => {
    const frame = document.createElement('iframe')
    frame.title = 'Invoice print report'
    frame.tabIndex = -1
    frame.setAttribute('aria-hidden', 'true')
    frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:210mm;height:297mm;border:0;'
    let finished = false
    let reportWindow

    const cleanup = () => {
      clearTimeout(loadTimeout)
      reportWindow?.removeEventListener('afterprint', onAfterPrint)
      frame.remove()
    }
    const fail = () => {
      if (finished) return
      finished = true
      cleanup()
      reject(new Error('The invoice report could not be printed'))
    }
    const onAfterPrint = () => {
      if (finished) return
      finished = true
      cleanup()
      resolve(true)
    }
    const loadTimeout = setTimeout(fail, 30000)

    frame.onerror = fail
    frame.onload = async () => {
      try {
        const report = frame.contentDocument
        // Error/login pages must never be printed or clear the saved form.
        if (!report?.querySelector('.invoice-preview-sheet')) return fail()
        reportWindow = frame.contentWindow
        await report.fonts.ready
        if (finished) return
        clearTimeout(loadTimeout)
        reportWindow.addEventListener('afterprint', onAfterPrint)
        reportWindow.focus()
        reportWindow.print()
      } catch {
        fail()
      }
    }
    frame.src = url
    document.body.appendChild(frame)
  })
}
