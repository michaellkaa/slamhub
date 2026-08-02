/* Custom service worker for PWA + Web Push */
import { clientsClaim } from 'workbox-core'
import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching'
import { NavigationRoute, registerRoute } from 'workbox-routing'

self.skipWaiting()
clientsClaim()

precacheAndRoute(self.__WB_MANIFEST || [])
cleanupOutdatedCaches()

try {
  registerRoute(
    new NavigationRoute(createHandlerBoundToURL('/'), {
      denylist: [/^\/api\//, /^\/storage\//, /^\/build\//],
    })
  )
} catch (e) {
  // Ignore if app shell route isn't available during early builds.
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
