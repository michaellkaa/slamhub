<template>
  <div class="bg-[#0f0f12] w-screen min-h-screen flex flex-col lg:flex-row">
    
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

            <div v-if="!loading && pastEvents.length" class="my-8 text-center text-white/60 text-sm tracking-wide">
              ------------ past events ------------
            </div>

            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              
              <template v-if="loading">
                <EventCard v-for="i in 3" :key="'skeleton-past-' + i" :loading="true" />
              </template>

              <template v-else>
                <EventCard
                  v-for="event in pastEvents"
                  :key="event.id"
                  :event="event"
                  @click="$router.push({ name: 'EventDetail', params: { id: event.id }})"
                />
              </template>

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

function handleNavigate(nav) {
  activeNav.value = nav
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/events')
    events.value = res.data
  } catch (err) {
    console.error('Chyba při načítání eventů:', err)
  } finally {
    loading.value = false
  }
})

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