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

export function isPushEnabledLocally() {
  try {
    return localStorage.getItem(PUSH_ENABLED_KEY) === '1'
  } catch {
    return false
  }
}

async function getRegistration() {
  if (!('serviceWorker' in navigator)) return null

  try {
    const existing = await navigator.serviceWorker.getRegistration()
    if (existing) return existing
  } catch {
    // continue
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
    }
  }

  const permission = Notification.permission
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

  // Recover: active browser subscription means notifications are on.
  if (permission === 'granted' && hasSubscription && !locallyEnabled) {
    try {
      localStorage.setItem(PUSH_ENABLED_KEY, '1')
      locallyEnabled = true
    } catch {
      locallyEnabled = true
    }
  }

  return {
    supported: true,
    enabled: locallyEnabled && permission === 'granted',
    permission,
    locallyEnabled,
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
    return { ok: false, reason: 'subscribe-failed' }
  }
}

export async function disablePushNotifications() {
  localStorage.removeItem(PUSH_ENABLED_KEY)

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
  if (!isPushEnabledLocally()) return
  if (!isPushSupported()) return
  if (!localStorage.getItem('token')) return
  if (Notification.permission !== 'granted') return

  try {
    const registration = await getRegistration()
    if (!registration) return

    let subscription = await registration.pushManager.getSubscription()
    if (!subscription) {
      const publicKey = await fetchVapidPublicKey()
      if (!publicKey) return
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey),
      })
    }

    await syncSubscription(subscription)
    localStorage.setItem(PUSH_ENABLED_KEY, '1')
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
    getPushEnabledState,
    getPushPermission,
    enablePushNotifications,
    disablePushNotifications,
    syncPushSubscriptionIfGranted,
  }
}
