<template>
  <div class="min-h-screen bg-[#0f0f12] text-white px-4 py-6 flex justify-center">
    <div class="w-full max-w-md">
      <button @click="$router.back()" class="mb-6 text-pink-500 hover:underline text-lg">
        ←
      </button>

      <div class="bg-[#121218] rounded-2xl p-5 shadow-[0_0_0_1px_rgba(255,255,255,0.06)]">
        <h1 class="text-2xl font-extrabold tracking-wide mb-2">Hlasovani</h1>

        <p class="text-white/70 text-sm mb-4">
          {{ headerText }}
        </p>

        <div v-if="!joined" class="space-y-3">
          <input v-model="joinCode" maxlength="6" placeholder="Kod hlasovani"
            class="w-full bg-[#0f0f12] rounded-xl px-4 py-3 uppercase outline-none shadow-[0_0_0_1px_rgba(236,72,153,0.35)] focus:shadow-[0_0_0_1px_rgba(236,72,153,0.8)]" />

          <div v-if="currentUser" class="flex items-center gap-2 text-sm text-white/80">
            <input id="anonVote" type="checkbox" v-model="joinAsAnonymous" />
            <label for="anonVote">Hlasovat anonymne</label>
          </div>

          <input v-if="!currentUser || joinAsAnonymous" v-model="nickname" maxlength="60"
            placeholder="Prezdivka (volitelne)"
            class="w-full bg-[#0f0f12] rounded-xl px-4 py-3 outline-none shadow-[0_0_0_1px_rgba(255,255,255,0.08)] focus:shadow-[0_0_0_1px_rgba(236,72,153,0.6)]" />

          <button @click="joinVoting" :disabled="!sessionStatus.enabled"
            class="w-full py-3 rounded-xl font-extrabold bg-pink-500 hover:bg-pink-600 transition shadow-[0_0_24px_rgba(236,72,153,0.25)] disabled:opacity-50">
            Vstoupit
          </button>
        </div>

        <div v-else class="space-y-4">
          <div class="bg-[#0f0f12] rounded-2xl p-4 shadow-[0_0_0_1px_rgba(236,72,153,0.2)]">
            <div class="text-xs text-white/60 mb-1">Prave hlasujete pro</div>
            <div class="text-lg font-extrabold">
              {{ liveRound.current_round?.performer_name || '—' }}
            </div>
          </div>

          <div class="grid grid-cols-5 gap-2">
            <button v-for="n in 10" :key="n" @click="castVote(n)" :disabled="disableVote"
              class="aspect-square rounded-2xl bg-[#0f0f12] font-extrabold text-2xl shadow-[0_0_0_2px_rgba(236,72,153,1)] hover:bg-pink-500/10 active:scale-[0.99] transition disabled:opacity-50">
              {{ n }}
            </button>
          </div>

          <div v-if="liveRound.already_voted" class="text-sm text-white/70">
            Hlas byl odeslan.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const eventId = computed(() => route.params.id)

const participantTokenKey = computed(() => `vote_participant_token_event_${eventId.value}`)

const joinCode = ref('')
const nickname = ref('')
const joinAsAnonymous = ref(true)
const joined = ref(false)

const sessionStatus = ref({ enabled: false, status: 'draft' })
const liveRound = ref({ current_round: null, already_voted: false, enabled: false, status: 'draft' })

const cachedUser = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
})

const currentUser = ref(null)

let timer = null
let inFlight = false
let cooldownUntil = 0
const ACTIVE_POLL_MS = 4000
const HIDDEN_POLL_MS = 12000
const pageVisible = ref(true)

const headerText = computed(() => {
  if (!sessionStatus.value.enabled) return 'Hlasovani neni aktivni.'
  if (!joined.value) return 'Zadejte kod a ohodnotte 1–10 bodu.'
  return 'Zvolte body 1–10.'
})

const disableVote = computed(() => {
  return (
    !sessionStatus.value.enabled ||
    !liveRound.value.current_round ||
    liveRound.value.current_round.state !== 'live' ||
    liveRound.value.already_voted
  )
})

const fetchStatus = async () => {
  const res = await axios.get(`/api/events/${eventId.value}/voting/status`)
  sessionStatus.value = res.data
}

const poll = async () => {
  if (inFlight) return
  if (Date.now() < cooldownUntil) return

  inFlight = true
  try {
    const res = await axios.get(`/api/events/${eventId.value}/voting/live-round`)
    liveRound.value = res.data
    sessionStatus.value.enabled = res.data.enabled
    sessionStatus.value.status = res.data.status
  } catch (err) {
    const status = err?.response?.status
    if (status === 429) {
      cooldownUntil = Date.now() + 20000
      startPolling()
      return
    }
    if (status === 503) {
      cooldownUntil = Date.now() + 15000
      startPolling()
      return
    }
    console.error('Vote page poll failed:', err)
  } finally {
    inFlight = false
  }
}

const startPolling = () => {
  if (timer) clearInterval(timer)
  const interval = pageVisible.value ? ACTIVE_POLL_MS : HIDDEN_POLL_MS
  timer = setInterval(poll, interval)
}

const onVisibility = () => {
  pageVisible.value = document.visibilityState === 'visible'
  startPolling()
}

const joinVoting = async () => {
  const res = await axios.post('/api/voting/join', {
    code: joinCode.value.toUpperCase().trim(),
    is_anonymous: !currentUser.value ? true : joinAsAnonymous.value,
    nickname: nickname.value || null,
  })

  const token = res?.data?.participant_token
  if (token) {
    localStorage.setItem(participantTokenKey.value, token)
    axios.defaults.headers.common['X-Vote-Participant-Token'] = token
  }
  joined.value = true
  await poll()
}

const castVote = async (points) => {
  const token = localStorage.getItem(participantTokenKey.value)
  await axios.post(`/api/events/${eventId.value}/voting/cast`, { vote_value: points, participant_token: token || null })
  await poll()
}

onMounted(async () => {
  try {
    const me = await axios.get('/api/me')
    currentUser.value = me.data
  } catch {
    currentUser.value = cachedUser.value
  }

  const existingToken = localStorage.getItem(participantTokenKey.value)
  if (existingToken) {
    axios.defaults.headers.common['X-Vote-Participant-Token'] = existingToken
  }

  await fetchStatus()
  startPolling()
  document.addEventListener('visibilitychange', onVisibility)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
  timer = null
  document.removeEventListener('visibilitychange', onVisibility)
})
</script>
