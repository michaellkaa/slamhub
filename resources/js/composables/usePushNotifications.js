import axios from 'axios'

const PUSH_ENABLED_KEY = 'push_enabled'
const SW_URL = '/service-worker.js'
const SW_WAIT_MS = 15000

function urlBase64ToUint8Array(base64String) {
  const cleaned = String(base64String || '').trim().replace(/\s+/g, '')
  const padding = '='.repeat((4 - (cleaned.length % 4)) % 4)
  const base64 = (cleaned + padding).replace(/-/g, '+').replace(/_/g, '/')
  const raw = window.atob(base64)
  const output = new Uint8Array(raw.length)
  for (let i = 0; i < raw.length; i += 1) {
    output[i] = raw.charCodeAt(i)
  }
  return output
}

function withTimeout(promise, ms, label = 'timeout') {
  return Promise.race([
    promise,
    new Promise((_, reject) => {
      window.setTimeout(() => reject(new Error(label)), ms)
    }),
  ])
}

function sleep(ms) {
  return new Promise((resolve) => window.setTimeout(resolve, ms))
}

export function isPushSupported() {
  return (
    typeof window !== 'undefined'
    && window.isSecureContext
    && 'serviceWorker' in navigator
    && 'PushManager' in window
    && 'Notification' in window
  )
}

export function isPushEnabledLocally() {
  try {
    return localStorage.getItem(PUSH_ENABLED_KEY) === '1'
  } catch {
    return false
  }
}

export function isPushExplicitlyDisabled() {
  try {
    return localStorage.getItem(PUSH_ENABLED_KEY) === '0'
  } catch {
    return false
  }
}

export function wantsPushEnabled() {
  if (isPushExplicitlyDisabled()) return false
  if (isPushEnabledLocally()) return true
  try {
    return typeof Notification !== 'undefined' && Notification.permission === 'granted'
  } catch {
    return false
  }
}

async function waitForActiveWorker(registration) {
  if (registration.active) return registration.active

  const worker = registration.installing || registration.waiting
  if (!worker) {
    await withTimeout(navigator.serviceWorker.ready, SW_WAIT_MS, 'sw-timeout')
    return registration.active
  }

  await withTimeout(new Promise((resolve) => {
    if (worker.state === 'activated') {
      resolve()
      return
    }
    worker.addEventListener('statechange', () => {
      if (worker.state === 'activated') resolve()
    })
  }), SW_WAIT_MS, 'sw-activate-timeout')

  return registration.active
}

/** Always register the Laravel-served SW (correct scope + no Cloudflare cache). */
export async function ensureServiceWorker() {
  if (!('serviceWorker' in navigator)) return null

  try {
    let registration = await navigator.serviceWorker.getRegistration('/')

    const scriptURL = registration?.active?.scriptURL
      || registration?.waiting?.scriptURL
      || registration?.installing?.scriptURL
      || ''

    const isOurSw = scriptURL.includes('service-worker.js')

    if (!registration || !isOurSw) {
      registration = await navigator.serviceWorker.register(SW_URL, {
        scope: '/',
        updateViaCache: 'none',
      })
    } else {
      try {
        await registration.update()
      } catch {
        // ignore
      }
    }

    window.__slamSwRegistration = registration
    await waitForActiveWorker(registration)
    await withTimeout(navigator.serviceWorker.ready, SW_WAIT_MS, 'sw-timeout')
    return registration
  } catch (err) {
    console.error('[PWA] Failed to register', SW_URL, err)
    return null
  }
}

async function getRegistration() {
  return ensureServiceWorker()
}

async function fetchVapidPublicKey() {
  const { data } = await axios.get('/api/push/vapid-public-key')
  return String(data?.publicKey || '').trim() || null
}

async function syncSubscription(subscription) {
  if (!subscription) return
  const json = subscription.toJSON()
  if (!json?.endpoint || !json?.keys?.p256dh || !json?.keys?.auth) {
    throw new Error('Incomplete push subscription keys')
  }
  await axios.post('/api/push/subscribe', {
    endpoint: json.endpoint,
    keys: {
      p256dh: json.keys.p256dh,
      auth: json.keys.auth,
    },
    contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
  })
}

export async function getPushEnabledState() {
  if (!isPushSupported()) {
    return {
      supported: false,
      enabled: false,
      permission: typeof window !== 'undefined' && !window.isSecureContext
        ? 'insecure'
        : 'unsupported',
      vapidOk: false,
      hasSubscription: false,
      locallyEnabled: false,
      explicitlyDisabled: false,
    }
  }

  const permission = Notification.permission
  const explicitlyDisabled = isPushExplicitlyDisabled()
  let locallyEnabled = isPushEnabledLocally()
  let vapidOk = true
  let hasSubscription = false

  try {
    await fetchVapidPublicKey()
  } catch {
    vapidOk = false
  }

  try {
    const registration = await getRegistration()
    const subscription = await registration?.pushManager?.getSubscription?.()
    hasSubscription = Boolean(subscription)
  } catch {
    hasSubscription = false
  }

  if (permission === 'granted' && !explicitlyDisabled && !locallyEnabled) {
    try {
      localStorage.setItem(PUSH_ENABLED_KEY, '1')
      locallyEnabled = true
    } catch {
      locallyEnabled = true
    }
  }

  return {
    supported: true,
    enabled: permission === 'granted' && !explicitlyDisabled && (locallyEnabled || hasSubscription),
    permission,
    locallyEnabled,
    explicitlyDisabled,
    hasSubscription,
    vapidOk,
  }
}

export async function enablePushNotifications() {
  if (!isPushSupported()) {
    return {
      ok: false,
      reason: typeof window !== 'undefined' && !window.isSecureContext ? 'insecure' : 'unsupported',
    }
  }

  if (!localStorage.getItem('token')) {
    return { ok: false, reason: 'unauthenticated' }
  }

  let permission = Notification.permission
  if (permission === 'default') {
    permission = await Notification.requestPermission()
  }
  if (permission !== 'granted') {
    return { ok: false, reason: 'denied' }
  }

  const registration = await getRegistration()
  if (!registration?.pushManager) {
    return { ok: false, reason: 'no-sw' }
  }

  await waitForActiveWorker(registration)

  let publicKey
  try {
    publicKey = await fetchVapidPublicKey()
  } catch {
    return { ok: false, reason: 'no-vapid' }
  }
  if (!publicKey) {
    return { ok: false, reason: 'no-vapid' }
  }

  const appServerKey = urlBase64ToUint8Array(publicKey)
  if (appServerKey.byteLength !== 65) {
    console.error('Invalid VAPID public key length', appServerKey.byteLength)
    return { ok: false, reason: 'no-vapid' }
  }

  try {
    // Prefer existing subscription — forced unsubscribe often causes Chrome AbortError.
    let subscription = await registration.pushManager.getSubscription()
    if (!subscription) {
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appServerKey,
      })
    }

    await syncSubscription(subscription)
    localStorage.setItem(PUSH_ENABLED_KEY, '1')
    return { ok: true, reason: 'subscribed' }
  } catch (err) {
    console.error('Push subscribe failed:', err)

    // One recovery attempt: drop old sub and retry after short wait.
    try {
      const existing = await registration.pushManager.getSubscription()
      if (existing) await existing.unsubscribe()
      await sleep(400)
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appServerKey,
      })
      await syncSubscription(subscription)
      localStorage.setItem(PUSH_ENABLED_KEY, '1')
      return { ok: true, reason: 'subscribed' }
    } catch (retryErr) {
      console.error('Push resubscribe failed:', retryErr)
      const message = String(retryErr?.message || err?.message || '')
      if (/push service error/i.test(message) || retryErr?.name === 'AbortError') {
        return { ok: false, reason: 'push-service' }
      }
      return { ok: false, reason: 'subscribe-failed' }
    }
  }
}

export async function disablePushNotifications() {
  try {
    localStorage.setItem(PUSH_ENABLED_KEY, '0')
  } catch {
    // ignore
  }

  try {
    await axios.delete('/api/push/subscribe')
  } catch {
    // ignore
  }

  if (!isPushSupported()) {
    return { ok: true, reason: 'unsubscribed' }
  }

  try {
    const registration = await getRegistration()
    const subscription = await registration?.pushManager?.getSubscription?.()
    if (subscription) {
      try {
        await axios.delete('/api/push/subscribe', {
          data: { endpoint: subscription.endpoint },
        })
      } catch {
        // ignore
      }
      await subscription.unsubscribe()
    }
  } catch (err) {
    console.error('Push unsubscribe failed:', err)
  }

  return { ok: true, reason: 'unsubscribed' }
}

export async function syncPushSubscriptionIfGranted() {
  if (!wantsPushEnabled()) return
  if (!isPushSupported()) return
  if (!localStorage.getItem('token')) return
  if (Notification.permission !== 'granted') return

  try {
    await enablePushNotifications()
  } catch (err) {
    console.error('Push sync failed:', err)
  }
}

export function getPushPermission() {
  if (!isPushSupported()) {
    if (typeof window !== 'undefined' && !window.isSecureContext) return 'insecure'
    return 'unsupported'
  }
  return Notification.permission
}

export function usePushNotifications() {
  return {
    isPushSupported,
    isPushEnabledLocally,
    isPushExplicitlyDisabled,
    wantsPushEnabled,
    ensureServiceWorker,
    getPushEnabledState,
    getPushPermission,
    enablePushNotifications,
    disablePushNotifications,
    syncPushSubscriptionIfGranted,
  }
}
