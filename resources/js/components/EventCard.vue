<template>
  <div class="bg-[#1a1a1d] border border-[#26262b] rounded-xl overflow-hidden flex flex-col">
    <template v-if="loading">
      <div class="h-28 bg-[#2a2a2e] animate-pulse"></div>
      <div class="p-4 space-y-3 flex-1">
        <div class="w-3/4 h-4 bg-[#2a2a2e] rounded animate-pulse"></div>
        <div class="w-1/2 h-3 bg-[#2a2a2e] rounded animate-pulse"></div>
        <div class="w-1/3 h-3 bg-[#2a2a2e] rounded animate-pulse"></div>
        <div class="w-full h-8 bg-[#2a2a2e] rounded mt-4 animate-pulse"></div>
      </div>
    </template>

    <template v-else>
      <div v-if="event.cover_image">
        <img :src="event.cover_image" class="h-28 w-full object-cover" />
      </div>
      <div class="p-4 space-y-2 flex-1">
        <h2 class="text-white font-bold text-lg truncate">{{ event.title }}</h2>
        <p class="text-white/60 text-sm truncate">{{ formatDate(event.starts_at) }}</p>
        <p class="text-white/70 text-sm truncate">{{ event.location }}</p>
        
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  event: { type: Object, required: false, default: () => ({}) },
  loading: { type: Boolean, default: true }
})

const formatDate = (value) => {
  if (!value) return ''
  const date = new Date(value)
  return date.toLocaleString()
}
</script>

<style scoped>
.animate-pulse {
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
