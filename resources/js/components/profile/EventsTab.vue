<template>
  <div class="mt-8 w-full lg:w-[50%] space-y-4">
    <div v-if="loading" class="space-y-3">
      <div
        v-for="n in 4"
        :key="'event-skeleton-' + n"
        class="rounded-2xl border border-white/10 bg-[#1d1d21] p-4 animate-pulse"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="space-y-2.5 flex-1">
            <div class="h-5 w-2/3 bg-white/10 rounded"></div>
            <div class="h-4 w-1/2 bg-white/10 rounded"></div>
          </div>
          <div class="h-6 w-20 bg-white/10 rounded-full"></div>
        </div>
        <div class="mt-3 h-3 w-1/3 bg-white/10 rounded"></div>
      </div>
    </div>

    <div v-else-if="sortedEvents.length" class="space-y-3">
      <a
        v-for="event in upcomingEvents"
        :key="event.id"
        :href="`/events/${event.id}`"
        class="block rounded-2xl border border-white/10 bg-[#1d1d21] p-4 transition hover:bg-[#24242a] hover:border-white/20"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-white font-semibold text-base truncate">
              {{ event.title || 'Bez nazvu' }}
            </div>
            <div class="mt-1 text-sm text-white/60 truncate">
              {{ event.location || 'Místo není uvedeno' }}
            </div>
          </div>

          <span
            class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold"
            :class="isUpcoming(event.starts_at) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10 text-white/70'"
          >
            {{ isUpcoming(event.starts_at) ? 'Nadcházející' : 'Proběhlo' }}
          </span>
        </div>

        <div class="mt-3 text-xs text-white/50">
          {{ formatDateTime(event.starts_at) }}
        </div>
      </a>

      <div v-if="pastEvents.length" class="pt-1">
        <button
          type="button"
          class="text-[11px] text-white/30 hover:text-white/50 transition"
          @click="showPastEvents = !showPastEvents"
        >
          {{ showPastEvents ? 'Skrýt proběhlé akce' : `Zobrazit proběhle akce (${pastEvents.length})` }}
        </button>
      </div>

      <div v-if="showPastEvents" class="space-y-2">
        <a
          v-for="event in pastEvents"
          :key="'past-' + event.id"
          :href="`/events/${event.id}`"
          class="block rounded-xl border border-white/5 bg-[#141418] p-3 opacity-80 hover:opacity-100 transition"
        >
          <div class="text-sm text-white/80 truncate">{{ event.title || 'Bez nazvu' }}</div>
          <div class="text-xs text-white/45 mt-1">{{ formatDateTime(event.starts_at) }}</div>
        </a>
      </div>
    </div>

    <div v-else class="rounded-xl border border-white/10 bg-[#17171b] p-4 text-white/50 text-sm">
      Tento uzivatel zatim nema zadne eventy.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: String
})

const events = ref([])
const loading = ref(true)
const sortedEvents = ref([])
const upcomingEvents = ref([])
const pastEvents = ref([])
const showPastEvents = ref(false)

const loadEvents = async () => {
  if (!props.username) return
  loading.value = true

  try {
    const res = await axios.get(`/api/users/${props.username}/events`)
    events.value = Array.isArray(res.data) ? res.data : []
    sortedEvents.value = [...events.value].sort((a, b) => {
      const left = new Date(a?.starts_at || 0).getTime()
      const right = new Date(b?.starts_at || 0).getTime()
      return right - left
    })
    upcomingEvents.value = sortedEvents.value.filter((event) => isUpcoming(event?.starts_at))
    pastEvents.value = sortedEvents.value.filter((event) => !isUpcoming(event?.starts_at))
    showPastEvents.value = false
  } catch (e) {
    console.error(e)
    events.value = []
    sortedEvents.value = []
    upcomingEvents.value = []
    pastEvents.value = []
    showPastEvents.value = false
  } finally {
    loading.value = false
  }
}

onMounted(loadEvents)
watch(() => props.username, loadEvents)

const isUpcoming = (dateValue) => {
  const time = new Date(dateValue || 0).getTime()
  if (Number.isNaN(time)) return false
  return time >= Date.now()
}

const formatDateTime = (dateValue) => {
  const date = new Date(dateValue || 0)
  if (Number.isNaN(date.getTime())) return 'Datum neni k dispozici'
  return new Intl.DateTimeFormat('cs-CZ', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}
</script>
