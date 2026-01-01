<template>
  <div class="mt-8 w-[50%] space-y-4">

    <div
      v-for="event in events"
      :key="event.id"
      class="p-4 bg-[#1d1d21] rounded-xl"
    >
      <a
        :href="`/events/${event.id}`"
        class="font-medium hover:text-pink-400 transition"
      >
        {{ event.title }}
      </a>

      <div class="text-sm text-white/40 mt-1">
        {{ event.location }}
      </div>

      <div class="text-xs text-white/30 mt-1">
        {{ formatDate(event.starts_at) }}
      </div>
    </div>

    <div v-if="!events.length" class="text-white/40 text-sm">
      Zatím jsi nevytvořil žádné eventy.
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const events = ref([])

onMounted(async () => {
  const res = await axios.get('/api/profile/events')
  events.value = res.data
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('cs-CZ')
}
</script>
