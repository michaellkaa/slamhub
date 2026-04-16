<template>
  <div class="min-h-screen bg-[#0f0f12] text-white px-4 py-6 flex justify-center">
    <div class="w-full max-w-2xl">
      <div class="flex items-center justify-between mb-6">
        <button @click="$router.back()" class="text-pink-500 hover:underline text-lg">
          ←
        </button>
        <div class="text-sm text-white/60">
          Host panel
        </div>
      </div>

      <div class="p-5">
        <div class="flex items-start justify-between gap-4 mb-5">
          <div>
            <h1 class="text-2xl font-extrabold tracking-wide">Hlasování (host)</h1>
          </div>
          <div class="text-right">
            <div class="text-xs text-white/50">Kód</div>
            <div class="font-mono text-lg font-bold">{{ session.code || '—' }}</div>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-6">

          <button @click="toggleVoting(true)" class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-200">
            Zapnout
          </button>
          <button @click="toggleVoting(false)" class="px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-200">
            Vypnout
          </button>
          <button @click="rotateCode" class="px-4 py-2 rounded-xl bg-pink-500/20 hover:bg-pink-500/30 text-pink-200">
            Nový kod
          </button>
          <button @click="finalizeWinner" class="px-4 py-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200">
            Připsat bod vítězi a vypnout
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
          <div class="bg-[#0f0f12] rounded-2xl p-4 shadow-[0_0_0_1px_rgba(255,255,255,0.06)]">
            <div class="text-xs text-white/50 mb-1">Stav</div>
            <div class="font-bold">{{ session.enabled ? 'Aktivni' : 'Neaktivni' }}</div>
          </div>
          <div class="bg-[#0f0f12] rounded-2xl p-4 shadow-[0_0_0_1px_rgba(255,255,255,0.06)]">
            <div class="text-xs text-white/50 mb-1">Hlasů (live)</div>
            <div class="font-extrabold text-xl">{{ liveTotals.votes }}</div>
          </div>
          <div class="bg-[#0f0f12] rounded-2xl p-4 shadow-[0_0_0_1px_rgba(255,255,255,0.06)]">
            <div class="text-xs text-white/50 mb-1">Součet bodů (live)</div>
            <div class="font-extrabold text-xl">{{ liveTotals.score }}</div>
          </div>
        </div>

        <div class="flex gap-2 mb-4">
          <input
            v-model="newPerformerName"
            placeholder="Jméno performera"
            class="flex-1 bg-[#0f0f12] rounded-xl px-4 py-3 outline-none shadow-[0_0_0_1px_rgba(255,255,255,0.08)] focus:shadow-[0_0_0_1px_rgba(236,72,153,0.6)]"
          />
          <button
            @click="createRound"
            class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 font-semibold"
          >
            Přidat kolo
          </button>
        </div>

        <div v-if="rounds.length" class="space-y-2">
          <div
            v-for="r in rounds"
            :key="r.id"
            class="bg-[#0f0f12] rounded-2xl px-4 py-3 flex items-center justify-between gap-3 shadow-[0_0_0_1px_rgba(255,255,255,0.06)]"
          >
            <div class="min-w-0">
              <div class="font-semibold truncate">{{ r.performer_name }}</div>
              <div class="text-xs text-white/50">{{ r.state }}</div>
              <div class="text-xs" :class="r.include_in_ranking ? 'text-emerald-300' : 'text-white/45'">
                {{ r.include_in_ranking ? 'Pocita se do vysledku' : 'Skryto (bez bodu)' }}
              </div>
            </div>
            <div class="flex gap-2 shrink-0">
              <button
                @click="toggleRoundVisibility(r)"
                class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20"
              >
                {{ r.include_in_ranking ? 'Skryt' : 'Zobrazit' }}
              </button>
              <button @click="startRound(r.id)" class="px-3 py-2 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-200">
                Start
              </button>
              <button @click="closeRound(r.id)" class="px-3 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-200">
                Close
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-sm text-white/60">
          Zatím žádná kola. Přidat prvního performera.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const eventId = route.params.id

const session = ref({})
const rounds = ref([])
const liveTotals = ref({ votes: 0, score: 0 })
const newPerformerName = ref('')

let timer = null
let inFlight = false
let cooldownUntil = 0

const ensureSession = async () => {
  const res = await axios.post(`/api/events/${eventId}/voting/session`)
  session.value = res.data
  rounds.value = res.data.rounds || []
}

const toggleVoting = async (enabled) => {
  await axios.patch(`/api/events/${eventId}/voting/session/toggle`, { enabled })
  await ensureSession()
}

const rotateCode = async () => {
  const res = await axios.post(`/api/events/${eventId}/voting/session/rotate-code`)
  session.value.code = res.data.code
}

const createRound = async () => {
  if (!newPerformerName.value.trim()) return
  const res = await axios.post(`/api/events/${eventId}/voting/rounds`, {
    performer_name: newPerformerName.value.trim(),
  })
  rounds.value = [res.data, ...rounds.value]
  newPerformerName.value = ''
}

const toggleRoundVisibility = async (round) => {
  await axios.patch(`/api/events/${eventId}/voting/rounds/${round.id}/visibility`, {
    include_in_ranking: !round.include_in_ranking,
  })
  await pollLive()
}

const startRound = async (roundId) => {
  await axios.post(`/api/events/${eventId}/voting/rounds/${roundId}/start`)
  await pollLive()
}

const closeRound = async (roundId) => {
  await axios.post(`/api/events/${eventId}/voting/rounds/${roundId}/close`)
  await pollLive()
}

const finalizeWinner = async () => {
  await axios.post(`/api/events/${eventId}/voting/finalize`)
  await pollLive()
}

const pollLive = async () => {
  if (inFlight) return
  if (Date.now() < cooldownUntil) return

  inFlight = true
  try {
    const res = await axios.get(`/api/events/${eventId}/voting/results/live`)
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

onMounted(async () => {
  await ensureSession()
  await pollLive()
  timer = setInterval(pollLive, 4000)
})

onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
  timer = null
})
</script>
