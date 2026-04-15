<template>
  <div>
    <router-view />
    <PwaUpdatePrompt v-if="isProd" />
  </div>
</template>


<script setup>
import PwaUpdatePrompt from './components/PwaUpdatePrompt.vue'

const isProd = import.meta.env.PROD
</script>