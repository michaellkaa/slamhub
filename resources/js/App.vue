<template>
  <div>
    <router-view />
    <ToastHost />
    <PwaUpdatePrompt v-if="isProd" />
  </div>
</template>


<script setup>
import { onMounted } from 'vue'
import PwaUpdatePrompt from './components/PwaUpdatePrompt.vue'
import ToastHost from './components/ToastHost.vue'
import { syncPushSubscriptionIfGranted } from './composables/usePushNotifications'

const isProd = import.meta.env.PROD

onMounted(() => {
  window.setTimeout(() => {
    syncPushSubscriptionIfGranted()
  }, 1500)
})
</script>
