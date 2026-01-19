<template>
  <div class="bg-[#0f0f12] min-h-screen text-white px-4 py-8 flex justify-center">
    <div class="w-full max-w-3xl">

      <button @click="$router.back()" class="mb-6 text-pink-500 hover:underline">
        ← Zpět
      </button>

      <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-40 bg-[#2a2a2e] rounded"></div>
        <div class="h-7 bg-[#2a2a2e] rounded w-3/4 mx-auto"></div>
        <div class="h-4 bg-[#2a2a2e] rounded w-1/2 mx-auto"></div>
      </div>

      <div v-else class="text-center">
        <img :src="coverSrc" class="w-full max-w-md h-80 object-cover rounded mx-auto mb-6" />


        <h1 class="text-2xl font-bold mb-2">
          {{ event.title || 'Bez názvu' }}
        </h1>

        <div class="text-white/60 text-sm mb-4 space-y-1">
          <p v-if="event.starts_at">
            {{ formatDate(event.starts_at) }}
          </p>
          <p v-if="event.location">
            {{ event.location }}
          </p>
        </div>

        <p v-if="event.description" class="text-white/80 leading-relaxed mb-6 text-left">
          {{ event.description }}
        </p>

        <div v-if="event.performers?.length" class="mb-8 text-left">
          <h2 class="text-lg font-semibold mb-3 text-left">
            Účinkující
          </h2>

          <ul class="space-y-2">
            <li v-for="performer in event.performers" :key="performer.id" class="flex items-center gap-3">
              <img :src="performer.profile_pic_url" class="w-8 h-8 rounded-full object-cover" />
              <span class="text-white/80">
                {{ performer.name || performer.username }}
              </span>
            </li>
          </ul>
        </div>

        <a v-if="event.ticket_url" :href="event.ticket_url" target="_blank"
          class="inline-block bg-pink-500 hover:bg-pink-600 transition px-6 py-2 rounded font-semibold">
          Koupit lístek
        </a>
      </div>
    </div>
  </div>
</template>


<script setup>
  //az event skonci tak organizator muze pridat vyherce pokud nebylo hlasovani pres mobil
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
})

const event = ref({
  title: '',
  description: '',
  starts_at: '',
  location: '',
  ticket_url: '',
  cover_image: null,
  performers: []
})

const loading = ref(true)

const formatDate = (value) => {
  return new Date(value).toLocaleString('cs-CZ', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

const coverSrc = computed(() => {
  return event.value.cover_image
    ? `/storage/${event.value.cover_image}`
    : '/storage/events/slam.jpg'
})

onMounted(async () => {
  try {
    const res = await axios.get(`/api/events/${props.id}`)
    event.value = res.data
  } catch (err) {
    console.error('Chyba při načítání eventu:', err)
  } finally {
    loading.value = false
  }
})
</script>
