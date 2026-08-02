<template>
  <div>
    <router-view />
    <ToastHost />
    <PwaUpdatePrompt v-if="isProd" />
  </div>
</template>


<script setup>
import { onMounted } from 'vue'
import { registerSW } from 'virtual:pwa-register'
import PwaUpdatePrompt from './components/PwaUpdatePrompt.vue'
import ToastHost from './components/ToastHost.vue'
import { syncPushSubscriptionIfGranted } from './composables/usePushNotifications'

const isProd = import.meta.env.PROD

registerSW({
  immediate: true,
  onRegisteredSW(swUrl, registration) {
    if (registration) {
      window.__slamSwRegistration = registration
    }
    console.info('[PWA] service worker registered:', swUrl)
  },
  onRegisterError(error) {
    console.error('[PWA] service worker registration failed:', error)
  },
})

onMounted(() => {
  window.setTimeout(() => {
    syncPushSubscriptionIfGranted()
  }, 1500)
})
</script>
