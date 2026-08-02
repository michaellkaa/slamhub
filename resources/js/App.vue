<template>
  <div>
    <router-view />
    <ToastHost />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import ToastHost from './components/ToastHost.vue'
import {
  ensureServiceWorker,
  syncPushSubscriptionIfGranted,
} from './composables/usePushNotifications'

onMounted(async () => {
  await ensureServiceWorker()
  window.setTimeout(() => {
    syncPushSubscriptionIfGranted()
  }, 1200)
})
</script>
