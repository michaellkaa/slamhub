<template>
  <div class="w-screen h-screen flex items-center justify-center bg-[#0f0f12] text-white p-4">
    <div class="bg-[#141418] rounded-xl shadow-xl p-8 w-full max-w-lg">
      <h1 class="text-2xl font-bold mb-6 text-center">Přidat Event</h1>
      
      <form @submit.prevent="submitEvent" class="flex flex-col gap-4">
        <label class="text-sm">Název eventu <span class="text-pink-400">*</span></label>
        <input
          v-model="event.title"
          required
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        />

        <label class="text-sm">Datum začátku <span class="text-pink-400">*</span></label>
        <input
          type="datetime-local"
          v-model="event.starts_at"
          required
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        />

        <label class="text-sm">Místo <span class="text-pink-400">*</span></label>
        <input
          v-model="event.location"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        />

        <label class="text-sm">Popis</label>
        <textarea
          v-model="event.description"
          rows="2"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        ></textarea>

        <label class="text-sm">Odkaz na lístky</label>
        <input
          v-model="event.ticket_url"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        />

        <label class="text-sm">Obrázek eventu</label>
        <input type="file" @change="uploadCover" class="p-3 rounded bg-[#1d1d21]" />

        <button
          type="button"
          @click="showModal = true"
          class="p-3 rounded bg-[#1d1d21] text-left hover:ring-2 hover:ring-pink-500"
        >
          Vybrat performery
          <span v-if="selectedPerformersLabel" class="text-white/60 text-sm">
            – {{ selectedPerformersLabel }}
          </span>
        </button>

        <button
        type="button"
        @click="showAwardModal = true"
        class="p-3 rounded bg-[#1d1d21] text-left hover:ring-2 hover:ring-pink-500"
        >
        Přidat ocenění
        <span v-if="selectedAwardsLabel" class="text-white/60 text-sm">
            – {{ selectedAwardsLabel }}
        </span>
        </button>


        <button
          type="submit"
          class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md"
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
  //opravit enter
  //pridat pocet znaku kdyz pisu popisek
  //presmerovani
  // Column 'location' cannot be null
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import PerfModal from '../components/PerfModal.vue'
import AwardModal from '../components/AwardModal.vue'

const event = ref({
  title: '',
  description: '',
  starts_at: '',
  ends_at: '',
  location: '',
  ticket_url: '',
  cover_image: null,
  performers: []
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

  for (const key in event.value) {
    if (key === 'performers') {
      event.value.performers.forEach(id => formData.append('performers[]', id))
    } else if (key === 'cover_image' && event.value.cover_image) {
      formData.append('cover_image', event.value.cover_image)
    } else {
      formData.append(key, event.value[key] ?? '')
    }
  }

  guestPerformers.value.forEach((guest, i) => {
    formData.append(`guest_performers[${i}]`, guest)
  })

  selectedAwards.value.forEach(id => {
    formData.append('awards[]', id)
  })

  try {
    const userRes = await axios.get('/api/me')
    formData.append('user_id', userRes.data.id)
  } catch {
    console.warn('User není přihlášený, user_id nebude odesláno.')
  }

  try {
    await axios.post('/api/events', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert('Event vytvořen!')
    event.value = {
      title: '',
      description: '',
      starts_at: '',
      ends_at: '',
      location: '',
      ticket_url: '',
      cover_image: null,
      performers: []
    }
    guestPerformers.value = []
  } catch (err) {
    console.error(err.response?.data)
    alert(JSON.stringify(err.response?.data))
  }
}

</script>
