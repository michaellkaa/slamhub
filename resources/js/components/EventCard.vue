<template>
  <div class="bg-[#1a1a1d] border border-[#26262b] rounded-xl overflow-hidden flex flex-col cursor-pointer"
    @click="$emit('click')">
    <template v-if="loading">
      <div class="h-28 bg-[#2a2a2e] animate-pulse"></div>
      <div class="p-4 space-y-3 flex-1">
        <div class="w-3/4 h-4 bg-[#2a2a2e] rounded animate-pulse"></div>
        <div class="w-1/2 h-3 bg-[#2a2a2e] rounded animate-pulse"></div>
        <div class="w-1/3 h-3 bg-[#2a2a2e] rounded animate-pulse"></div>
      </div>
    </template>

    <template v-else>
      <template v-if="event.cover_image">
        <img :src="`/storage/${event.cover_image}`" class="h-28 w-full object-cover" />
      </template>
      <template v-else>
        <div class="h-28 w-full bg-[#1d1d20]"></div>
      </template>

      <div class="p-4 space-y-2 flex-1">
        <h2 class="text-white font-bold text-lg truncate">
          {{ event.title || 'Bez názvu' }}
        </h2>
        <p class="text-white/60 text-sm truncate">
          {{ formatDate(event.starts_at) || '-' }}
        </p>
        <p class="text-white/70 text-sm truncate">
          {{ event.location || '-' }}
        </p>
      </div>
    </template>
  </div>
</template>

<script setup>
const props = defineProps({
  event: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false }
})

const formatDate = (value) => {
  if (!value) return ''
  const d = new Date(value)

  const date = d.toLocaleDateString([], {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })

  const time = d.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit'
  })

  return `${date} ${time}`
}
</script>

<style scoped>
.animate-pulse {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.4;
  }
}
</style>
