/* Custom service worker for PWA + Web Push — keep install lightweight so subscribe works. */
import { clientsClaim } from 'workbox-core'
import { cleanupOutdatedCaches, precacheAndRoute } from 'workbox-precaching'

self.skipWaiting()
clientsClaim()

self.addEventListener('message', (event) => {
  if (event?.data?.type === 'SKIP_WAITING') {
    self.skipWaiting()
  }
})

// Precache built assets, but don't hard-fail the worker if a file is missing.
try {
  precacheAndRoute(self.__WB_MANIFEST || [])
  cleanupOutdatedCaches()
} catch (e) {
  console.warn('[SW] precache skipped', e)
}

self.addEventListener('push', (event) => {
  let payload = {
    title: 'SlamHub',
    body: 'Nová notifikace',
    url: '/',
  }

  try {
    if (event.data) {
      const parsed = event.data.json()
      payload = { ...payload, ...parsed }
    }
  } catch {
    try {
      const text = event.data?.text?.() || ''
      if (text) payload.body = text
    } catch {
      // ignore
    }
  }

  event.waitUntil(
    self.registration.showNotification(payload.title || 'SlamHub', {
      body: payload.body || '',
      icon: '/pwa-icon.svg',
      badge: '/pwa-icon.svg',
      data: {
        url: payload.url || '/',
      },
    })
  )
})

self.addEventListener('notificationclick', (event) => {
  event.notification.close()
  const targetUrl = event.notification?.data?.url || '/'

  event.waitUntil(
    (async () => {
      const allClients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      for (const client of allClients) {
        if ('focus' in client) {
          await client.focus()
          if ('navigate' in client) {
            await client.navigate(targetUrl)
          }
          return
        }
      }
      if (self.clients.openWindow) {
        await self.clients.openWindow(targetUrl)
      }
    })()
  )
})
