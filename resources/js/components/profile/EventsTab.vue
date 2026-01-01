<template>
  <div class="mt-8 w-[50%] space-y-4">

    <div
      v-for="event in events"
      :key="event.id"
      class="p-4 bg-[#1d1d21] rounded-xl"
    >
      <a
        :href="`/events/${event.slug}`"
        class="font-medium hover:text-pink-400 transition"
      >
        {{ event.name }}
      </a>

      <div class="text-sm text-white/40 mt-1">
        {{ event.place }}
      </div>

      <div class="text-xs text-white/30 mt-1">
        {{ event.date }}
      </div>
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
</script>
