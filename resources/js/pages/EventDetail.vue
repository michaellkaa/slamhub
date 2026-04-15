<template>
  <div class="bg-[#0f0f12] min-h-screen text-white px-4 py-8 flex justify-center">
    <div class="w-full max-w-3xl">

      <button @click="$router.back()" class="mb-6 text-pink-500 hover:underline text-lg">
        ← 
      </button>

      <div v-if="loading" class="animate-pulse space-y-4">
        <div class="w-full max-w-md h-80 bg-[#2a2a2e] rounded mx-auto"></div>
        <div class="h-8 bg-[#2a2a2e] rounded w-3/4 mx-auto"></div>
        <div class="h-4 bg-[#2a2a2e] rounded w-1/2 mx-auto"></div>
        <div class="h-12 bg-[#2a2a2e] rounded w-64 mx-auto"></div>
        <div class="space-y-2 pt-2">
          <div class="h-4 bg-[#2a2a2e] rounded w-full"></div>
          <div class="h-4 bg-[#2a2a2e] rounded w-5/6"></div>
        </div>
      </div>

      <div v-else class="text-center">
        <img
          v-if="event.cover_image"
          :src="`/storage/${event.cover_image}`"
          class="w-full max-w-md h-80 object-cover rounded mx-auto mb-6"
        />

        <h1 class="text-2xl font-bold mb-2">
          {{ event.title || 'Bez názvu' }}
        </h1>

        <div class="text-white/60 text-sm mb-4 space-y-1">
          <p v-if="event.starts_at">
            {{ formatDate(event.starts_at) }}
          </p>
          <p>
            Typ: {{ event.event_mode === 'league' ? 'League' : 'Regular' }}
          </p>
          <p v-if="event.location">
            {{ event.location }}
          </p>
        </div>

        <div class="mb-6">
          <button
          @click="sessionStatus.enabled && router.push({ name: 'EventVote', params: { id: props.id } })"            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-extrabold transition disabled:opacity-50 disabled:cursor-not-allowed"
            :class="sessionStatus.enabled
              ? 'bg-pink-500 hover:bg-pink-600 shadow-[0_0_24px_rgba(236,72,153,0.25)]'
              : 'bg-white/10'"
            :disabled="!sessionStatus.enabled"
          >
          
            Hlasovani
            <span v-if="!sessionStatus.enabled" class="text-white/60 text-sm font-semibold">(Neaktivni)</span>
          </button>
        </div>

        <p v-if="event.description" class="text-white/80 leading-relaxed mb-6 text-left whitespace-pre-line">
          {{ event.description }}
        </p>

        <div v-if="event.performers?.length" class="mb-8 text-left">
          <h2 class="text-lg font-semibold mb-3 text-left">
            Účinkující
          </h2>

          <ul class="space-y-2">
            <li
            v-for="performer in event.performers"
            :key="performer.id"
            class="flex items-center gap-3 cursor-pointer"
            @click="goToProfile(performer.username)"
          >
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

        <div class="mt-8 bg-[#121218] rounded-2xl p-5 text-left shadow-[0_0_0_1px_rgba(255,255,255,0.06)]">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h3 class="text-lg font-extrabold">Hlasovani</h3>
              <p class="text-sm text-white/60">
                {{ sessionStatus.enabled ? 'Aktivni' : 'Neaktivni' }}
              </p>
              <div class="mt-2">
                <span
                  class="inline-flex items-center gap-2 text-xs font-semibold px-2.5 py-1 rounded-full"
                  :class="canManageVoting ? 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-white/5 text-white/50 ring-1 ring-white/10'"
                >
                  Host: {{ canManageVoting ? 'On' : 'Off' }}
                </span>
              </div>
            </div>

            <button
              v-if="canManageVoting"
              @click="$router.push({ name: 'EventVoteHost', params: { id: props.id } })"
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10"
            >
              Host panel
            </button>
            <button
              v-if="canManageLeagueSpider && event.event_mode === 'league'"
              @click="$router.push({ name: 'EventLeagueHost', params: { id: props.id } })"
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10"
            >
              League spider
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
  //az event skonci tak organizator muze pridat vyherce pokud nebylo hlasovani pres mobil
// link na performer profile!! ne na muj
import { ref, onMounted, computed, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

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
const sessionStatus = ref({ enabled: false, status: 'draft' })
let votingTimer = null
let pollInFlight = false
let cooldownUntilTs = 0
const ACTIVE_POLL_MS = 4000
const HIDDEN_POLL_MS = 12000
const isPageVisible = ref(true)

const cachedUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
})

const currentUser = ref(null)

const canManageVoting = computed(() => {
  const role = currentUser.value?.role || cachedUser.value?.role
  if (!role) return false
  if (role === 'admin' || role === 'moderator') return true
  const userId = currentUser.value?.id || cachedUser.value?.id
  return role === 'organizer' && Number(userId) === Number(event.value?.user_id)
})

const canManageLeagueSpider = computed(() => {
  const role = currentUser.value?.role || cachedUser.value?.role
  if (role !== 'organizer') return false
  const userId = currentUser.value?.id || cachedUser.value?.id
  return Number(userId) === Number(event.value?.user_id)
})

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
    try {
      const me = await axios.get('/api/me')
      currentUser.value = me.data
    } catch {
      currentUser.value = cachedUser.value
    }

    const res = await axios.get(`/api/events/${props.id}`)
    event.value = res.data
    await fetchVotingStatus()
    if (canManageVoting.value) {
      await ensureSession()
    }
    startVotingPolling()
    document.addEventListener('visibilitychange', handleVisibilityChange)
  } catch (err) {
    console.error('Chyba při načítání eventu:', err)
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => {
  if (votingTimer) {
    clearInterval(votingTimer)
    votingTimer = null
  }
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})

const goToProfile = (username) => {
  router.push(`/profile/${username}`)
}

const fetchVotingStatus = async () => {
  const res = await axios.get(`/api/events/${props.id}/voting/status`)
  sessionStatus.value = res.data
}

const pollLiveState = async () => {
  if (pollInFlight) return
  if (Date.now() < cooldownUntilTs) return

  pollInFlight = true
  try {
    await fetchVotingStatus()
  } catch (err) {
    const status = err?.response?.status
    if (status === 429) {
      const retryAfterHeader = Number(err?.response?.headers?.['retry-after'])
      const retryMs = Number.isFinite(retryAfterHeader) && retryAfterHeader > 0
        ? retryAfterHeader * 1000
        : 20000
      cooldownUntilTs = Date.now() + retryMs
      startVotingPolling()
      return
    }
    console.error('Voting poll failed:', err)
  } finally {
    pollInFlight = false
  }
}

const startVotingPolling = () => {
  if (votingTimer) clearInterval(votingTimer)
  const intervalMs = isPageVisible.value ? ACTIVE_POLL_MS : HIDDEN_POLL_MS
  votingTimer = setInterval(pollLiveState, intervalMs)
}

const handleVisibilityChange = () => {
  isPageVisible.value = document.visibilityState === 'visible'
  startVotingPolling()
}

const ensureSession = async () => {
  await axios.post(`/api/events/${props.id}/voting/session`)
}

</script>
