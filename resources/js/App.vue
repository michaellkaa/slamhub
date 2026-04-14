<template>
  <div>
    <router-view />
    <PwaUpdatePrompt />
  </div>
</template>


<script setup>
import PwaUpdatePrompt from './components/PwaUpdatePrompt.vue'
</script>