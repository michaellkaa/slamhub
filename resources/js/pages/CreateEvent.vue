<template>
  <div class="w-screen min-h-screen flex items-center justify-center bg-[#0f0f12] text-white p-6">

    <div class="bg-[#141418] rounded-2xl shadow-2xl p-8 w-full max-w-2xl space-y-8">

      <div class="text-center">
        <h1 class="text-3xl font-bold">
  {{ isEdit ? 'Upravit Event' : 'Přidat Event' }}
</h1>

<p class="text-white/40 text-sm mt-1">
  {{
    isEdit
      ? 'Uprav detaily existujícího eventu'
      : 'Vytvoř nový event a nastav jeho detaily'
  }}
</p>
      </div>

      <form @submit.prevent="submitEvent" class="space-y-8">

        <div class="space-y-4">

          <input v-model="event.title" required placeholder="Název eventu"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none" />

          <input type="datetime-local" v-model="event.starts_at" required
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none" />

          <input v-model="event.location" placeholder="Místo konání"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none" />

          <textarea v-model="event.description" rows="3" placeholder="Popis eventu"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none" />
        </div>

        <div class="space-y-1">

          <input type="file" @change="uploadCover" class="w-full p-3 rounded-lg bg-[#1d1d21]" />
        </div>

        <div class="space-y-4">

          <select v-model="event.event_mode"
            class="w-full p-3 rounded-lg bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none">
            <option value="regular">Regular</option>
            <option value="league">League</option>
          </select>




        </div>

        <div class="space-y-3">

          <button type="button" @click="showModal = true"
            class="w-full p-3 rounded-lg bg-[#1d1d21] hover:ring-2 hover:ring-pink-500 text-left transition">
            Vybrat performery
            <div v-if="selectedPerformersLabel" class="text-xs text-white/40 mt-1">
              {{ selectedPerformersLabel }}
            </div>
          </button>

          <button type="button" @click="showAwardModal = true"
            class="w-full p-3 rounded-lg bg-[#1d1d21] hover:ring-2 hover:ring-pink-500 text-left transition">
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
  {{ isEdit ? 'Uložit změny' : 'Vytvořit event' }}
</button>

      </form>

      <PerfModal v-model="showModal" :performers="performers" :selected-performers="event.performers"
        :guest-performers="guestPerformers" @update:performers="updatePerformers" @update:guests="updateGuests" />

      <AwardModal v-model="showAwardModal" :awards="awards" :selected-awards="selectedAwards"
        @update:selectedAwards="handleAwardUpdate" />
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter, useRoute, onBeforeRouteLeave } from 'vue-router'
import axios from 'axios'

import PerfModal from '../components/PerfModal.vue'
import AwardModal from '../components/AwardModal.vue'

axios.defaults.withCredentials = true

const router = useRouter()
const route = useRoute()

const eventId = route.params.id || null
const isEdit = computed(() => !!eventId)

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

const performers = ref([])
const awards = ref([])
const guestPerformers = ref([])

const selectedAwards = ref([])

const showModal = ref(false)
const showAwardModal = ref(false)

const isDirty = ref(false)
const isSubmitting = ref(false)
let initialized = false

watch(
  event,
  () => {
    if (!initialized) return
    isDirty.value = true
  },
  { deep: true }
)

onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie')

    const [pRes, aRes] = await Promise.all([
      axios.get('/api/performers'),
      axios.get('/api/awards')
    ])

    performers.value = pRes.data
    awards.value = aRes.data

    if (isEdit.value) {
      const res = await axios.get(`/api/events/${eventId}`)
      const data = res.data

      event.value = {
        ...event.value,
        ...data,
        starts_at: data.starts_at?.slice(0, 16) || '',
        ends_at: data.ends_at?.slice(0, 16) || '',
        performers: data.performers?.map(p => p.id) || [],
        winner_award_id: data.winner_award_id || null
      }

      guestPerformers.value = data.guest_performers || []
      selectedAwards.value = data.winner_award_id ? [data.winner_award_id] : []
    }

    initialized = true
    isDirty.value = false
  } catch (err) {
    console.error(err)
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

const handleAwardUpdate = (val) => {
  selectedAwards.value = val;
  if (val.length > 0) {
    event.value.is_award_event = true;
  }
};

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
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const awardId = selectedAwards.value.length > 0 ? selectedAwards.value[0] : null;
    event.value.winner_award_id = awardId;

    event.value.is_award_event = !!awardId;

    const formData = new FormData();

    formData.append('starts_at', event.value.starts_at);
    formData.append('ends_at', event.value.ends_at || '');
    formData.append('is_award_event', event.value.is_award_event ? '1' : '0');
    formData.append('winner_award_id', event.value.winner_award_id ?? '');

    for (const key in event.value) {
      const skipKeys = [
        'performers', 'cover_image', 'starts_at', 'ends_at',
        'is_award_event', 'winner_award_id'
      ];

      if (!skipKeys.includes(key)) {
        formData.append(key, event.value[key] ?? '');
      }
    }

    event.value.performers.forEach(id => formData.append('performers[]', id));

    guestPerformers.value.forEach((g, i) => formData.append(`guest_performers[${i}]`, g));

    if (event.value.cover_image instanceof File) {
      formData.append('cover_image', event.value.cover_image);
    }

    if (isEdit.value) {
      formData.append('_method', 'PUT');
    }

    const url = isEdit.value ? `/api/events/${eventId}` : '/api/events';

    await axios.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    isDirty.value = false;
    router.push('/events');

  } catch (err) {
    console.error("Chyba při ukládání:", err.response?.data);
    alert("Chyba: " + JSON.stringify(err.response?.data?.errors || err.response?.data?.message));
  } finally {
    isSubmitting.value = false;
  }
};

onBeforeRouteLeave(() => {
  if (!isDirty.value) return true
  return confirm('Máte neuložené změny. Opravdu odejít?')
})

const beforeUnloadHandler = (e) => {
  if (!isDirty.value) return
  e.preventDefault()
  e.returnValue = ''
}

onMounted(() => {
  window.addEventListener('beforeunload', beforeUnloadHandler)
})

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', beforeUnloadHandler)
})
</script>