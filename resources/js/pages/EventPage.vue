<template>
  <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">
    
    <div class="lg:h-full lg:w-28 w-full fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex flex-col mt-4 lg:mt-28 min-h-0 pb-28 lg:pb-0">
      
      <div class="w-full flex-1 min-h-0 flex justify-center px-4">
        <div class="max-w-5xl w-full flex flex-col flex-1 min-h-0">

          <section class="mt-4 mb-2 text-center space-y-3">
            <h1 class="text-white text-2xl font-bold">Události</h1>
          </section>

          <div class="flex-1 overflow-y-auto min-h-0 pb-6 scrollbar-hide">
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              
              <template v-if="loading">
                <EventCard v-for="i in 3" :key="'skeleton-' + i" :loading="true" />
              </template>

              <template v-else>
                <EventCard
                  v-for="event in events"
                  :key="event.id"
                  :event="event"
                  @click="$router.push({ name: 'EventDetail', params: { id: event.id }})"
                />
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

const activeNav = ref('award')
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
</script>

<style>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>