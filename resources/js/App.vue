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

// Register immediately so push subscribe can find an active SW.
registerSW({ immediate: true })

onMounted(() => {
  window.setTimeout(() => {
    syncPushSubscriptionIfGranted()
  }, 1500)
})
</script>
