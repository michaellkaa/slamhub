<template>
  <div class="bg-[#0f0f12] w-full min-h-screen flex flex-col lg:flex-row overflow-x-hidden">
    
    <div class="lg:h-full lg:w-28 w-full fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex flex-col mt-4 lg:mt-28 pb-28 lg:pb-8">
      
      <div class="w-full flex justify-center px-4">
        <div class="max-w-5xl w-full flex flex-col">

          <section class="mt-4 mb-2 text-center space-y-3">
            <h1 class="text-white text-2xl font-bold">Události</h1>
          </section>

          <div class="pb-6">
            <div v-if="loadError" class="mb-6 rounded-xl border border-red-400/30 bg-red-500/10 p-4 text-center text-red-200">
              <p class="font-semibold">Nepodarilo se nacist udalosti.</p>
              <button
                class="mt-3 rounded-lg bg-red-500/80 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                @click="loadEvents"
              >
                Zkusit znovu
              </button>
            </div>

            <section v-if="upcomingEvents.length || loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <template v-if="loading">
                <EventCard v-for="i in 3" :key="'skeleton-upcoming-' + i" :loading="true" />
              </template>

              <template v-else>
                <EventCard
                  v-for="event in upcomingEvents"
                  :key="event.id"
                  :event="event"
                  @click="$router.push({ name: 'EventDetail', params: { id: event.id }})"
                />
              </template>
            </section>

            <section
              v-if="!loading && pastEvents.length"
              class="mt-10 rounded-2xl border border-white/10 bg-white/[0.03] p-3 md:p-4"
            >
              <button
                type="button"
                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-center hover:bg-white/10"
                @click="showPastEvents = !showPastEvents"
              >
                <div class="text-white text-base md:text-lg font-semibold">Minulé události</div>
                <div class="mt-0.5 text-white/55 text-sm">{{ pastEvents.length }} záznamů</div>
                <div class="mt-1 text-white/70 text-sm">{{ showPastEvents ? '▲ Skrýt' : '▼ Zobrazit' }}</div>
              </button>

              <section v-if="showPastEvents" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <EventCard
                  v-for="event in pastEvents"
                  :key="event.id"
                  :event="event"
                  @click="$router.push({ name: 'EventDetail', params: { id: event.id }})"
                />
              </section>
            </section>

            <div v-if="!loading && !upcomingEvents.length && !pastEvents.length" class="text-center text-white/60 mt-6">
              Zatím tu nejsou žádné eventy.
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import SideNav from '../components/SideNav.vue'
import EventCard from '../components/EventCard.vue'
import { computed, ref, onMounted } from 'vue'
import axios from 'axios'

const activeNav = ref('events')
const events = ref([])
const loading = ref(true)
const loadError = ref(false)
const showPastEvents = ref(false)

function handleNavigate(nav) {
  activeNav.value = nav
}

const normalizeEvents = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const loadEvents = async () => {
  loading.value = true
  loadError.value = false

  try {
    const res = await axios.get('/api/events')
    events.value = normalizeEvents(res.data)
  } catch (err) {
    console.error('Chyba při načítání eventů:', err)
    events.value = []
    loadError.value = true
  } finally {
    loading.value = false
  }
}

onMounted(loadEvents)

const upcomingEvents = computed(() => {
  const now = new Date()
  return events.value
    .filter((event) => event?.starts_at && new Date(event.starts_at) >= now)
    .sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at))
})

const pastEvents = computed(() => {
  const now = new Date()
  return events.value
    .filter((event) => !event?.starts_at || new Date(event.starts_at) < now)
    .sort((a, b) => new Date(b.starts_at || 0) - new Date(a.starts_at || 0))
})
</script>