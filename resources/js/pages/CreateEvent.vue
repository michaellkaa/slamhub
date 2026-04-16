<template>
  <div class="w-screen min-h-screen flex items-center justify-center bg-[#0f0f12] text-white p-6">

    <div class="bg-[#141418] rounded-2xl shadow-2xl p-8 w-full max-w-2xl space-y-8">

      <div class="text-center">
        <h1 class="text-3xl font-bold">Přidat Event</h1>
        <p class="text-white/40 text-sm mt-1">Vytvoř nový event a nastav jeho detaily</p>
      </div>

      <form @submit.prevent="submitEvent" class="space-y-8">

        <div class="space-y-4">

          <input
            v-model="event.title"
            required
            placeholder="Název eventu"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          />

          <input
            type="datetime-local"
            v-model="event.starts_at"
            required
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          />

          <input
            v-model="event.location"
            placeholder="Místo konání"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          />

          <textarea
            v-model="event.description"
            rows="3"
            placeholder="Popis eventu"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          />
        </div>

        <div class="space-y-1">

          <input
            type="file"
            @change="uploadCover"
            class="w-full p-3 rounded-lg bg-[#1d1d21]"
          />
        </div>

        <div class="space-y-4">

          <select
            v-model="event.event_mode"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          >
            <option value="regular">Regular</option>
            <option value="league">League</option>
          </select>




        </div>

        <div class="space-y-3">

          <button
            type="button"
            @click="showModal = true"
            class="w-full p-3 rounded-lg bg-[#1d1d21] hover:ring-2 hover:ring-pink-500 text-left transition"
          >
            Vybrat performery
            <div v-if="selectedPerformersLabel" class="text-xs text-white/40 mt-1">
              {{ selectedPerformersLabel }}
            </div>
          </button>

          <button
            type="button"
            @click="showAwardModal = true"
            class="w-full p-3 rounded-lg bg-[#1d1d21] hover:ring-2 hover:ring-pink-500 text-left transition"
          >
            Přidat ocenění
            <div v-if="selectedAwardsLabel" class="text-xs text-white/40 mt-1">
              {{ selectedAwardsLabel }}
            </div>
          </button>
        </div>

        <button
          type="submit"
          class="w-full bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded-xl shadow-lg"
        >
          Vytvořit event
        </button>

      </form>

      <PerfModal
        v-model="showModal"
        :performers="performers"
        :selected-performers="event.performers"
        :guest-performers="guestPerformers"
        @update:performers="updatePerformers"
        @update:guests="updateGuests"
      />

      <AwardModal
        v-model="showAwardModal"
        :awards="awards"
        :selected-awards="selectedAwards"
        @update:selectedAwards="val => selectedAwards = val"
      />

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import PerfModal from '../components/PerfModal.vue'
import AwardModal from '../components/AwardModal.vue'

const router = useRouter()

const event = ref({
  title: '',
  description: '',
  starts_at: '',
  ends_at: '',
  location: '',
  ticket_url: '',
  cover_image: null,
  performers: [],
  event_mode: 'regular',
  is_award_event: false,
  winner_award_id: null
})

const awards = ref([])
const selectedAwards = ref([])
const showAwardModal = ref(false)

const performers = ref([])
const guestPerformers = ref([])
const showModal = ref(false)

onMounted(async () => {
  try {
    const resPerformers = await axios.get('/api/performers')
    performers.value = resPerformers.data

    const resAwards = await axios.get('/api/awards')
    awards.value = resAwards.data
  } catch (err) {
    console.error('Chyba při načítání performerů nebo awards:', err)
  }
})

const uploadCover = (e) => {
  const file = e.target.files[0]
  if (file) event.value.cover_image = file
}

const updatePerformers = (selected) => {
  event.value.performers = selected
}

const updateGuests = (guests) => {
  guestPerformers.value = guests
}

const selectedPerformersLabel = computed(() => {
  const names = performers.value
    .filter(p => event.value.performers.includes(p.id))
    .map(p => p.name)

  return [...names, ...guestPerformers.value].join(', ')
})

const selectedAwardsLabel = computed(() => {
  return awards.value
    .filter(a => selectedAwards.value.includes(a.id))
    .map(a => a.title)
    .join(', ')
})

const submitEvent = async () => {
  const formData = new FormData()

  formData.append('starts_at', event.value.starts_at)
  formData.append('ends_at', event.value.ends_at || '')

  for (const key in event.value) {
    if (key === 'performers') {
      event.value.performers.forEach(id =>
        formData.append('performers[]', id)
      )

    } else if (key === 'cover_image' && event.value.cover_image) {
      formData.append('cover_image', event.value.cover_image)

    } else if (key === 'is_award_event') {
      formData.append(
        'is_award_event',
        event.value.is_award_event ? 1 : 0
      )

    } else if (key === 'starts_at' || key === 'ends_at') {
      continue

    } else {
      formData.append(key, event.value[key] ?? '')
    }
  }

  guestPerformers.value.forEach((guest, i) => {
    formData.append(`guest_performers[${i}]`, guest)
  })

  try {
    const userRes = await axios.get('/api/me')
    formData.append('user_id', userRes.data.id)
  } catch {}

  try {
    await axios.post('/api/events', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    event.value = {
      title: '',
      description: '',
      starts_at: '',
      ends_at: '',
      location: '',
      ticket_url: '',
      cover_image: null,
      performers: [],
      event_mode: 'regular',
      is_award_event: false,
      winner_award_id: null
    }

    guestPerformers.value = []

    router.push('/events')

  } catch (err) {
    console.error(err.response?.data)
    alert(JSON.stringify(err.response?.data))
  }
}
</script>
