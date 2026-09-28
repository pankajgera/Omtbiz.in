import i18n from '@/plugins/i18n'

// Reserve the tab during the click, before report-link requests can lose user activation.
export async function openReportInNewTab (urlOrLoader) {
  if (!urlOrLoader) return false

  const tab = window.open('about:blank', '_blank')
  if (!tab) {
    window.toastr.error(i18n.global.t('reports.popup_blocked'))
    return false
  }

  tab.opener = null
  try {
    const url = typeof urlOrLoader === 'function' ? await urlOrLoader() : urlOrLoader
    if (!url || tab.closed) {
      if (!tab.closed) tab.close()
      return false
    }
    tab.location.replace(url)
    return true
  } catch (error) {
    if (!tab.closed) tab.close()
    window.toastr.error(i18n.global.t('reports.open_failed'))
    return false
  }
}
