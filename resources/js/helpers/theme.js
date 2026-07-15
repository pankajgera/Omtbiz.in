const STORAGE_KEY = 'omtbiz-theme'
const THEMES = ['light', 'dark']

export function getPreferredTheme () {
  const storedTheme = window.localStorage.getItem(STORAGE_KEY)

  if (THEMES.includes(storedTheme)) {
    return storedTheme
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export function getActiveTheme () {
  const activeTheme = document.documentElement.dataset.theme
  return THEMES.includes(activeTheme) ? activeTheme : getPreferredTheme()
}

export function applyTheme (theme, persist = false) {
  const nextTheme = THEMES.includes(theme) ? theme : 'light'
  document.documentElement.dataset.theme = nextTheme
  document.documentElement.style.colorScheme = nextTheme

  const themeColor = document.querySelector('meta[name="theme-color"]')
  themeColor?.setAttribute('content', nextTheme === 'dark' ? '#11161b' : '#f4f6f8')

  if (persist) {
    window.localStorage.setItem(STORAGE_KEY, nextTheme)
  }

  return nextTheme
}

export function toggleTheme () {
  return applyTheme(getActiveTheme() === 'dark' ? 'light' : 'dark', true)
}

export function handleStoredThemeChange (event) {
  if (event.key === STORAGE_KEY && THEMES.includes(event.newValue)) {
    return applyTheme(event.newValue)
  }

  return getActiveTheme()
}
