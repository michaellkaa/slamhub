<template>
  <div class="mt-8 w-[50%] space-y-4">

    <div v-if="loading" class="space-y-4">
      <div
        v-for="n in 3"
        :key="n"
        class="p-4 bg-[#1d1d21] rounded-xl animate-pulse space-y-2"
      >
        <div class="h-4 w-40 bg-white/10 rounded"></div>
        <div class="h-3 w-32 bg-white/10 rounded"></div>
        <div class="h-3 w-24 bg-white/10 rounded"></div>
      </div>
    </div>

    <div v-else-if="events.length">
      <div
        v-for="event in events"
        :key="event.id"
        class="p-4 my-4 bg-[#1d1d21] rounded-xl"
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
    </div>

    <div v-else class="text-white/40 text-sm">
      Tento uživatel zatím nemá žádné eventy, kde by byl performer.
    </div>

  </div>
</template>

<script setup>
  //ja chci at to je cele cliickable

import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: String
})

const events = ref([])
const loading = ref(true)

const loadEvents = async () => {
  if (!props.username) return
  loading.value = true

  try {
    const res = await axios.get(`/api/users/${props.username}/events`)
    events.value = res.data
  } catch (e) {
    console.error(e)
    events.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadEvents)
watch(() => props.username, loadEvents)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('cs-CZ')
}
</script>
