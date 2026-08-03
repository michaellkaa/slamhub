<template>
  <div>
    <div class="flex items-start justify-between gap-4 mb-5">
      <div>
        <h2 class="text-xl font-semibold">Hlasování</h2>
        <p class="text-app-muted text-sm mt-1">Zapni hlasování, přidej kola a spravuj výsledky.</p>
      </div>
      <div class="text-right shrink-0">
        <div class="text-xs text-app-muted">Kód</div>
        <div class="font-mono text-lg font-bold">{{ session.code || '—' }}</div>
      </div>
    </div>

    <div v-if="loading" class="text-sm text-app-muted py-6">Načítám hlasování…</div>

    <template v-else>
      <div class="flex flex-wrap gap-2 mb-6">
        <button
          type="button"
          @click="toggleVoting(true)"
          class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-700 dark:text-emerald-200 text-sm font-medium"
        >
          Zapnout
        </button>
        <button
          type="button"
          @click="toggleVoting(false)"
          class="px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-700 dark:text-rose-200 text-sm font-medium"
        >
          Vypnout
        </button>
        <button
          type="button"
          @click="rotateCode"
          class="px-4 py-2 rounded-xl bg-pink-500/20 hover:bg-pink-500/30 text-pink-700 dark:text-pink-200 text-sm font-medium"
        >
          Nový kód
        </button>
        <button
          type="button"
          @click="finalizeWinner"
          class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-700 dark:text-amber-200 text-sm font-medium"
        >
          Připsat bod vítězi a vypnout
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-app bg-surface p-4">
          <div class="text-xs text-app-muted mb-1">Stav</div>
          <div class="font-semibold">{{ session.enabled ? 'Aktivní' : 'Neaktivní' }}</div>
        </div>
        <div class="rounded-xl border border-app bg-surface p-4">
          <div class="text-xs text-app-muted mb-1">Hlasů (live)</div>
          <div class="font-semibold text-xl">{{ liveTotals.votes }}</div>
        </div>
        <div class="rounded-xl border border-app bg-surface p-4">
          <div class="text-xs text-app-muted mb-1">Součet bodů (live)</div>
          <div class="font-semibold text-xl">{{ liveTotals.score }}</div>
        </div>
      </div>

      <div class="flex gap-2 mb-4">
        <input
          v-model="newPerformerName"
          placeholder="Jméno performera"
          class="flex-1 h-10 px-3 rounded-md bg-surface text-app border border-app text-sm focus:outline-none focus:border-pink-500"
          @keydown.enter.prevent="createRound"
        />
        <button
          type="button"
          @click="createRound"
          class="px-4 py-2 rounded-md bg-surface-hover hover:bg-surface-active text-app text-sm font-semibold"
        >
          Přidat kolo
        </button>
      </div>

      <div v-if="rounds.length" class="space-y-2">
        <div
          v-for="r in rounds"
          :key="r.id"
          class="rounded-xl border border-app bg-surface px-4 py-3 flex items-center justify-between gap-3"
        >
          <div class="min-w-0">
            <div class="font-semibold truncate">{{ r.performer_name }}</div>
            <div class="text-xs text-app-muted">{{ r.state }}</div>
            <div class="text-xs" :class="r.include_in_ranking ? 'text-emerald-500' : 'text-app-muted'">
              {{ r.include_in_ranking ? 'Počítá se do výsledku' : 'Skryto (bez bodu)' }}
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <button
              type="button"
              @click="toggleRoundVisibility(r)"
              class="px-3 py-2 rounded-lg bg-app hover:bg-surface-hover text-sm"
            >
              {{ r.include_in_ranking ? 'Skrýt' : 'Zobrazit' }}
            </button>
            <button
              type="button"
              @click="startRound(r.id)"
              class="px-3 py-2 rounded-lg bg-sky-500/20 hover:bg-sky-500/30 text-sky-700 dark:text-sky-200 text-sm"
            >
              Start
            </button>
            <button
              type="button"
              @click="closeRound(r.id)"
              class="px-3 py-2 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-700 dark:text-rose-200 text-sm"
            >
              Close
            </button>
          </div>
        </div>
      </div>

      <div v-else class="text-sm text-app-muted py-4">
        Zatím žádná kola. Přidej prvního performera.
      </div>

      <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>
    </template>
  </div>
</template>

<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  eventId: { type: [String, Number], required: true },
})

const session = ref({})
const rounds = ref([])
const liveTotals = ref({ votes: 0, score: 0 })
const newPerformerName = ref('')
const loading = ref(true)
const error = ref('')

let timer = null
let inFlight = false
let cooldownUntil = 0

const stopPolling = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const ensureSession = async () => {
  const res = await axios.post(`/api/events/${props.eventId}/voting/session`)
  session.value = res.data
  rounds.value = res.data.rounds || []
}

const toggleVoting = async (enabled) => {
  error.value = ''
  try {
    await axios.patch(`/api/events/${props.eventId}/voting/session/toggle`, { enabled })
    await ensureSession()
  } catch (err) {
    console.error('Toggle voting failed', err)
    error.value = 'Nepodařilo se změnit stav hlasování.'
  }
}

const rotateCode = async () => {
  error.value = ''
  try {
    const res = await axios.post(`/api/events/${props.eventId}/voting/session/rotate-code`)
    session.value = { ...session.value, code: res.data.code }
  } catch (err) {
    console.error('Rotate code failed', err)
    error.value = 'Nepodařilo se obnovit kód.'
  }
}

const createRound = async () => {
  if (!newPerformerName.value.trim()) return
  error.value = ''
  try {
    const res = await axios.post(`/api/events/${props.eventId}/voting/rounds`, {
      performer_name: newPerformerName.value.trim(),
    })
    rounds.value = [res.data, ...rounds.value]
    newPerformerName.value = ''
  } catch (err) {
    console.error('Create round failed', err)
    error.value = 'Nepodařilo se přidat kolo.'
  }
}

const toggleRoundVisibility = async (round) => {
  try {
    await axios.patch(`/api/events/${props.eventId}/voting/rounds/${round.id}/visibility`, {
      include_in_ranking: !round.include_in_ranking,
    })
    await pollLive()
  } catch (err) {
    console.error('Toggle visibility failed', err)
  }
}

const startRound = async (roundId) => {
  try {
    await axios.post(`/api/events/${props.eventId}/voting/rounds/${roundId}/start`)
    await pollLive()
  } catch (err) {
    console.error('Start round failed', err)
  }
}

const closeRound = async (roundId) => {
  try {
    await axios.post(`/api/events/${props.eventId}/voting/rounds/${roundId}/close`)
    await pollLive()
  } catch (err) {
    console.error('Close round failed', err)
  }
}

const finalizeWinner = async () => {
  error.value = ''
  try {
    await axios.post(`/api/events/${props.eventId}/voting/finalize`)
    await pollLive()
  } catch (err) {
    console.error('Finalize failed', err)
    error.value = 'Finalizace selhala.'
  }
}

const pollLive = async () => {
  if (inFlight) return
  if (Date.now() < cooldownUntil) return

  inFlight = true
  try {
    const res = await axios.get(`/api/events/${props.eventId}/voting/results/live`)
    session.value = res.data.session || session.value
    rounds.value = res.data.rounds || rounds.value
    liveTotals.value = res.data.totals || liveTotals.value
  } catch (err) {
    const status = err?.response?.status
    if (status === 429) {
      cooldownUntil = Date.now() + 20000
      return
    }
    if (status === 503) {
      cooldownUntil = Date.now() + 15000
      return
    }
    console.error('Host poll failed:', err)
  } finally {
    inFlight = false
  }
}

const boot = async () => {
  stopPolling()
  loading.value = true
  error.value = ''
  session.value = {}
  rounds.value = []
  liveTotals.value = { votes: 0, score: 0 }
  newPerformerName.value = ''

  try {
    await ensureSession()
    await pollLive()
    timer = setInterval(pollLive, 4000)
  } catch (err) {
    console.error('Boot voting failed', err)
    error.value = 'Nepodařilo se načíst hlasování.'
  } finally {
    loading.value = false
  }
}

watch(
  () => props.eventId,
  (id) => {
    if (id) boot()
    else stopPolling()
  },
  { immediate: true },
)

onBeforeUnmount(stopPolling)
</script>
