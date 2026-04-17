<template>
  <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">
    <div class="lg:h-full lg:w-28 w-full  fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex flex-col pt-4 lg:pt-24 overflow-hidden min-h-0 pb-28 lg:pb-0">
      <div class="w-full flex-1 min-h-0 flex justify-center px-4">
        <div class="max-w-5xl w-full flex-1 min-h-0 flex flex-col">

          <section class="mt-4 mb-4 flex items-center justify-between gap-4">
            <h1 class="text-white text-2xl font-bold">Události</h1>

            <div class="relative">
              <button
                type="button"
                class="min-w-[180px] rounded-xl border border-white/10 bg-[#17171b] px-3 py-2 text-left text-sm text-white/90 hover:bg-[#1f1f24] transition"
                @click="isFilterOpen = !isFilterOpen"
              >
                {{ activeFilterLabel }}
                <span class="float-right text-white/50">▾</span>
              </button>

              <div
                v-if="isFilterOpen"
                class="absolute right-0 mt-2 w-full rounded-xl border border-white/10 bg-[#141418] p-1 shadow-xl z-30"
              >
                <button
                  v-for="option in filterOptions"
                  :key="option.value"
                  type="button"
                  class="w-full rounded-lg px-3 py-2 text-left text-sm transition"
                  :class="activeFilter === option.value ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                  @click="selectFilter(option.value)"
                >
                  {{ option.label }}
                </button>
              </div>
            </div>
          </section>

          <div class="flex-1 overflow-y-auto min-h-0 pb-6 events-scroll">
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <template v-if="loading">
                <EventCard v-for="i in 3" :key="'skeleton-' + i" :loading="true" />
              </template>

              <template v-else>
                <EventCard
                  v-for="event in filteredEvents"
                  :key="event.id"
                  :event="event"
                  :loading="false"
                  @click="$router.push({ name: 'EventDetail', params: { id: event.id }})"
                />

                <div
                  v-if="!filteredEvents.length"
                  class="col-span-full rounded-xl border border-white/10 bg-[#17171b] p-4 text-sm text-white/50"
                >
                  Pro tento filtr nejsou žádné události.
                </div>

              </template>
            </section>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SideNav from '../components/SideNav.vue'
import EventCard from '../components/EventCard.vue'
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'

const activeNav = ref('award')
const events = ref([])
const loading = ref(true)
const isFilterOpen = ref(false)
const activeFilter = ref('all')
const filterOptions = [
  { value: 'all', label: 'Všechny události' },
  { value: 'upcoming', label: 'Nadcházející' },
  { value: 'past', label: 'Minulé' },
]

function handleNavigate(nav) {
  activeNav.value = nav
}

const activeFilterLabel = computed(() => {
  return filterOptions.find((o) => o.value === activeFilter.value)?.label || 'Filtr'
})

const filteredEvents = computed(() => {
  const now = Date.now()
  if (activeFilter.value === 'upcoming') {
    return events.value.filter((event) => new Date(event?.starts_at || 0).getTime() >= now)
  }
  if (activeFilter.value === 'past') {
    return events.value.filter((event) => new Date(event?.starts_at || 0).getTime() < now)
  }
  return events.value
})

const selectFilter = (value) => {
  activeFilter.value = value
  isFilterOpen.value = false
}

const closeFilterOnOutsideClick = (event) => {
  const target = event.target
  if (!(target instanceof HTMLElement)) return
  if (target.closest('.relative')) return
  isFilterOpen.value = false
}

onMounted(async () => {
  document.addEventListener('click', closeFilterOnOutsideClick)
  try {
    const res = await axios.get('/api/events')
    events.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Chyba při načítání eventů:', err)
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeFilterOnOutsideClick)
})
</script>

<style scoped>
.events-scroll {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.events-scroll::-webkit-scrollbar {
  display: none;
}
</style>
