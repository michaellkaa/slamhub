import axios from 'axios'

const PUSH_ENABLED_KEY = 'push_enabled'
const SW_WAIT_MS = 10000

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
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

export function isPushSupported() {
  return (
    typeof window !== 'undefined'
    && window.isSecureContext
    && 'serviceWorker' in navigator
    && 'PushManager' in window
    && 'Notification' in window
  )
}

/** Explicit app preference: '1' on, '0' off, null = never chosen in app. */
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

/** Should the app keep / create a push subscription? */
export function wantsPushEnabled() {
  if (isPushExplicitlyDisabled()) return false
  if (isPushEnabledLocally()) return true
  // Browser already granted (e.g. via OS settings) → treat as opted in.
  try {
    return typeof Notification !== 'undefined' && Notification.permission === 'granted'
  } catch {
    return false
  }
}

async function getRegistration() {
  if (!('serviceWorker' in navigator)) return null

  // Prefer registration already created by virtual:pwa-register.
  if (window.__slamSwRegistration) {
    return window.__slamSwRegistration
  }

  try {
    const existing = await navigator.serviceWorker.getRegistration('/')
      || await navigator.serviceWorker.getRegistration()
    if (existing?.active || existing?.waiting || existing?.installing) {
      return existing
    }
  } catch {
    // continue to explicit register
  }

  // Production: SW is copied to /sw.js (root scope). Fallback: /build/sw.js + header.
  const candidates = import.meta.env.DEV
    ? []
    : ['/sw.js', '/build/sw.js']

  for (const url of candidates) {
    try {
      const reg = await navigator.serviceWorker.register(url, { scope: '/' })
      window.__slamSwRegistration = reg
      await withTimeout(navigator.serviceWorker.ready, SW_WAIT_MS, 'sw-timeout')
      return reg
    } catch (err) {
      console.warn('SW register failed for', url, err)
    }
  }

  try {
    return await withTimeout(navigator.serviceWorker.ready, SW_WAIT_MS, 'sw-timeout')
  } catch {
    return null
  }
}

async function fetchVapidPublicKey() {
  const { data } = await axios.get('/api/push/vapid-public-key')
  return data?.publicKey || null
}

async function syncSubscription(subscription) {
  if (!subscription) return
  const json = subscription.toJSON()
  await axios.post('/api/push/subscribe', {
    endpoint: json.endpoint,
    keys: {
      p256dh: json.keys?.p256dh,
      auth: json.keys?.auth,
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

  // Browser granted + not explicitly off in app → mark app preference ON.
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
    // Show ON when browser allows and user didn't turn off in app.
    // Subscription may still be healing in the background.
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
  if (!registration) {
    return { ok: false, reason: 'no-sw' }
  }

  let publicKey
  try {
    publicKey = await fetchVapidPublicKey()
  } catch {
    return { ok: false, reason: 'no-vapid' }
  }
  if (!publicKey) {
    return { ok: false, reason: 'no-vapid' }
  }

  try {
    let subscription = await registration.pushManager.getSubscription()

    // Re-subscribe if keys might be stale / missing.
    if (!subscription) {
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
      })
    }

    await syncSubscription(subscription)
    localStorage.setItem(PUSH_ENABLED_KEY, '1')
    return { ok: true, reason: 'subscribed' }
  } catch (err) {
    console.error('Push subscribe failed:', err)

    // Existing subscription may be bound to old VAPID — drop and retry once.
    try {
      const registration = await getRegistration()
      const existing = await registration?.pushManager?.getSubscription?.()
      if (existing) await existing.unsubscribe()

      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
      })
      await syncSubscription(subscription)
      localStorage.setItem(PUSH_ENABLED_KEY, '1')
      return { ok: true, reason: 'subscribed' }
    } catch (retryErr) {
      console.error('Push resubscribe failed:', retryErr)
      return { ok: false, reason: 'subscribe-failed' }
    }
  }
}

export async function disablePushNotifications() {
  // Explicit off so browser "granted" doesn't keep flipping UI back on.
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
    getPushEnabledState,
    getPushPermission,
    enablePushNotifications,
    disablePushNotifications,
    syncPushSubscriptionIfGranted,
  }
}
