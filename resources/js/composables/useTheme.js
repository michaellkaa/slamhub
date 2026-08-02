import { computed, ref } from 'vue'

export const THEME_STORAGE_KEY = 'theme'
export const THEME_OPTIONS = ['light', 'dark', 'system']

const preference = ref(readStoredPreference())
let mediaQuery = null
let mediaListener = null

function readStoredPreference() {
  try {
    const stored = localStorage.getItem(THEME_STORAGE_KEY)
    if (THEME_OPTIONS.includes(stored)) return stored
  } catch {
    // ignore
  }
  return 'dark'
}

function getSystemTheme() {
  if (typeof window === 'undefined' || !window.matchMedia) return 'dark'
  // Check light first so macOS/iOS/Windows light mode is unambiguous
  if (window.matchMedia('(prefers-color-scheme: light)').matches) return 'light'
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark'
  return 'dark'
}

export function resolveTheme(pref = preference.value) {
  if (pref === 'system') return getSystemTheme()
  if (pref === 'light' || pref === 'dark') return pref
  return 'dark'
}

function syncMetaThemeColor(resolved) {
  const meta = document.querySelector('meta[name="theme-color"]')
  if (!meta) return
  const bg = getComputedStyle(document.documentElement).getPropertyValue('--color-bg').trim()
  meta.setAttribute('content', bg || (resolved === 'dark' ? '#0f0f12' : '#f4f4f6'))
}

export function applyTheme(pref = preference.value) {
  const value = THEME_OPTIONS.includes(pref) ? pref : 'dark'
  const resolved = resolveTheme(value)
  const root = document.documentElement

  // data-theme stays as preference so "system" is handled by CSS @media
  root.setAttribute('data-theme', value)
  root.classList.remove('dark', 'light', 'system')
  root.classList.add(value)

  // For browsers/UI chrome that need an absolute light/dark class too
  root.classList.toggle('scheme-dark', resolved === 'dark')
  root.classList.toggle('scheme-light', resolved === 'light')

  syncMetaThemeColor(resolved)
  return resolved
}

function onSystemSchemeChange() {
  if (preference.value === 'system') {
    applyTheme('system')
  }
}

function bindSystemListener() {
  if (typeof window === 'undefined' || !window.matchMedia) return

  if (mediaQuery && mediaListener) {
    if (mediaQuery.removeEventListener) mediaQuery.removeEventListener('change', mediaListener)
    else if (mediaQuery.removeListener) mediaQuery.removeListener(mediaListener)
  }

  mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  mediaListener = onSystemSchemeChange

  if (mediaQuery.addEventListener) mediaQuery.addEventListener('change', mediaListener)
  else if (mediaQuery.addListener) mediaQuery.addListener(mediaListener)

  // Also listen to light query for broader browser support
  const lightQuery = window.matchMedia('(prefers-color-scheme: light)')
  if (lightQuery.addEventListener) lightQuery.addEventListener('change', mediaListener)
  else if (lightQuery.addListener) lightQuery.addListener(mediaListener)
}

export function initTheme() {
  preference.value = readStoredPreference()
  applyTheme(preference.value)
  bindSystemListener()
  return preference.value
}

export function setThemePreference(next) {
  const value = THEME_OPTIONS.includes(next) ? next : 'dark'
  preference.value = value

  try {
    localStorage.setItem(THEME_STORAGE_KEY, value)
  } catch {
    // ignore
  }

  applyTheme(value)
  return value
}

export function useTheme() {
  const resolvedTheme = computed(() => resolveTheme(preference.value))

  return {
    preference,
    resolvedTheme,
    setTheme: setThemePreference,
    applyTheme,
    options: THEME_OPTIONS,
  }
}
