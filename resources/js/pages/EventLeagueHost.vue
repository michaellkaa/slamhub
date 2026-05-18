<template>
  <div class="min-h-screen bg-[#0f0f12] text-white px-4 py-6 flex justify-center">
    <div class="w-full max-w-3xl">
      <div class="flex items-center justify-between mb-6">
        <button @click="$router.back()" class="text-pink-500 hover:underline text-lg">←</button>
        <div class="text-sm text-white/60">(host)</div>
      </div>
      <div class="rounded-2xl bg-[#121218] p-5 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div v-for="slot in localSlots" :key="slot.id" class="bg-[#0f0f12] rounded-xl p-3">
            <div class="text-xs text-white/60 mb-2">Soutěžící {{ slot.id }}</div>
            <select v-model="slot.value" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
              <option :value="null">Vyber osobu</option>
              <option v-for="p in allParticipants" :key="p.key" :value="p.label">{{ p.label }}</option>
            </select>
          </div>
        </div>
        <div class="space-y-3">
          <div v-for="m in matches" :key="m.id" class="bg-[#0f0f12] rounded-xl p-3">
            <div class="text-sm mb-2">{{ getSlot(m.left) }} vs {{ getSlot(m.right) }}</div>
            <select v-model="roundRobin[m.id]" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
              <option :value="null">Vyber vítěze</option>
              <option :value="getSlot(m.left)">{{ getSlot(m.left) }}</option>
              <option :value="getSlot(m.right)">{{ getSlot(m.right) }}</option>
            </select>
          </div>
        </div>
        <div class="bg-[#0f0f12] rounded-xl p-3">
          <div class="text-sm text-white/70 mb-2">2. kolo: 3. vs 2.</div>
          <select v-model="secondRoundWinner" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
            <option :value="null">Vyber vítěze</option>
            <option v-if="ranking[2]" :value="ranking[2]">{{ ranking[2] }}</option>
            <option v-if="ranking[1]" :value="ranking[1]">{{ ranking[1] }}</option>
          </select>
        </div>
        <div class="bg-[#0f0f12] rounded-xl p-3">
          <div class="text-sm text-white/70 mb-2">Finale: 1. vs vítěz 2. kola</div>
          <select v-model="finalWinner" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
            <option :value="null">Vyber vítěze ligy</option>
            <option v-if="ranking[0]" :value="ranking[0]">{{ ranking[0] }}</option>
            <option v-if="secondRoundWinner" :value="secondRoundWinner">{{ secondRoundWinner }}</option>
          </select>
        </div>
        <button @click="saveLeague"
          class="w-full rounded-xl bg-pink-500 hover:bg-pink-600 py-3 font-bold">Uložit</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const eventId = route.params.id
const localSlots = ref([{ id: 'A', value: null }, { id: 'B', value: null }, { id: 'C', value: null }])
const roundRobin = ref({ ab: null, bc: null, ca: null })
const secondRoundWinner = ref(null)
const finalWinner = ref(null)
const eventPerformers = ref([])
const guestPerformers = ref([])
const matches = [{ id: 'ab', left: 'A', right: 'B' }, { id: 'bc', left: 'B', right: 'C' }, { id: 'ca', left: 'C', right: 'A' }]

const allParticipants = computed(() => {
  const fromEvent = eventPerformers.value.map((p) => ({ key: `u-${p.id}`, label: `${p.name} (@${p.username})` }))
  const fromGuests = guestPerformers.value.map((g, idx) => ({ key: `g-${idx}`, label: g }))
  return [...fromEvent, ...fromGuests]
})

const getSlot = (id) => localSlots.value.find((s) => s.id === id)?.value || `Soutezici ${id}`

const ranking = computed(() => {
  const wins = {}
  localSlots.value.forEach((s) => { if (s.value) wins[s.value] = 0 })
  matches.forEach((m) => {
    const winner = roundRobin.value[m.id]
    if (winner && wins[winner] !== undefined) wins[winner] += 1
  })
  return Object.entries(wins).sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0])).map(([name]) => name)
})

const loadLeague = async () => {
  const res = await axios.get(`/api/events/${eventId}/league`)
  eventPerformers.value = res.data.event_performers || []
  guestPerformers.value = res.data.guest_performers || []
  const data = res.data.league_data || {}
  if (Array.isArray(data.slots)) localSlots.value = data.slots.map((s) => ({ id: s.id, value: s.performer_name || null }))
  roundRobin.value = { ab: null, bc: null, ca: null, ...(data.round_robin || {}) }
  secondRoundWinner.value = data.second_round_winner || null
  finalWinner.value = data.final_winner || null
}

const saveLeague = async () => {
  const payload = {
    slots: localSlots.value.map((s) => ({
      id: s.id,
      performer_id: null,
      performer_name: s.value || null
    })),
    round_robin: roundRobin.value,
    second_round_winner: secondRoundWinner.value,
    final_winner: finalWinner.value,
  }

  await axios.put(`/api/events/${eventId}/league`, {
    league_data: payload
  })

  router.back()
}

onMounted(loadLeague)
</script>
