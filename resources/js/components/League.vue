<template>
  <div class="w-full px-4 pb-28 md:pb-8 mt-4">
    <div class="mx-auto w-full max-w-3xl rounded-2xl bg-[#141418] border border-[#1f1f22] p-4 md:p-5">
      <div class="mb-5">
        <h2 class="text-[#FFF7CC] text-lg md:text-xl font-bold">Klasická liga (3 hráči)</h2>
        <p class="text-white/60 text-sm mt-1">A vs B, B vs C, C vs A → 3. vs 2. → finále s 1.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <div v-for="(slot, index) in slots" :key="index" class="rounded-xl bg-[#0f0f12] border border-[#1f1f22] p-3">
          <div class="text-white/60 text-xs mb-2">Soutěžící {{ slot.label }}</div>
          <select v-model="slot.mode" class="w-full mb-2 rounded-lg bg-[#1d1d21] p-2 text-sm">
            <option value="profile">Profil</option>
            <option value="name">Jméno</option>
          </select>

          <select
            v-if="slot.mode === 'profile'"
            v-model="slot.userId"
            class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm"
          >
            <option :value="null">Vyber profil</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} (@{{ u.username }})</option>
          </select>
          <input
            v-else
            v-model.trim="slot.name"
            class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm"
            placeholder="Napiš jméno"
          />
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-xl bg-[#0f0f12] border border-[#1f1f22] p-4">
          <div class="font-semibold text-[#FFF7CC] mb-3">1. kolo (každý s každým)</div>
          <div class="space-y-3">
            <div v-for="m in firstRoundMatches" :key="m.id" class="rounded-lg bg-[#141418] border border-[#1f1f22] p-3">
              <div class="text-sm text-white mb-2">{{ displayName(m.left) }} vs {{ displayName(m.right) }}</div>
              <select v-model="results[m.id]" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
                <option :value="null">Vyber vítěze</option>
                <option :value="m.left">{{ displayName(m.left) }}</option>
                <option :value="m.right">{{ displayName(m.right) }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-[#0f0f12] border border-[#1f1f22] p-4 text-sm space-y-3">
          <div class="font-semibold text-[#FFF7CC]">Postup</div>
          <div class="rounded-lg bg-[#141418] border border-[#1f1f22] p-3">
            <div class="text-white/70 mb-1">Pořadí po 1. kole</div>
            <div class="text-white">1. {{ ranking[0] ? displayName(ranking[0].key) : '—' }}</div>
            <div class="text-white">2. {{ ranking[1] ? displayName(ranking[1].key) : '—' }}</div>
            <div class="text-white">3. {{ ranking[2] ? displayName(ranking[2].key) : '—' }}</div>
          </div>

          <div class="rounded-lg bg-[#141418] border border-[#1f1f22] p-3">
            <div class="text-white/70 mb-1">2. kolo: 3. vs 2.</div>
            <select v-model="secondRoundWinner" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
              <option :value="null">Vyber vítěze 2. kola</option>
              <option v-if="thirdVsSecond.left" :value="thirdVsSecond.left">{{ displayName(thirdVsSecond.left) }}</option>
              <option v-if="thirdVsSecond.right" :value="thirdVsSecond.right">{{ displayName(thirdVsSecond.right) }}</option>
            </select>
          </div>

          <div class="rounded-lg bg-[#141418] border border-[#1f1f22] p-3">
            <div class="text-white/70 mb-1">Finále: 1. vs vítěz 2. kola</div>
            <select v-model="finalWinner" class="w-full rounded-lg bg-[#1d1d21] p-2 text-sm">
              <option :value="null">Vyber vítěze ligy</option>
              <option v-if="finalistFromFirst" :value="finalistFromFirst">{{ displayName(finalistFromFirst) }}</option>
              <option v-if="secondRoundWinner" :value="secondRoundWinner">{{ displayName(secondRoundWinner) }}</option>
            </select>
          </div>

          <div class="rounded-lg bg-pink-500/10 border border-pink-500/30 p-3">
            <div class="text-white/70 text-xs mb-1">Vítěz ligy</div>
            <div class="text-[#FFF7CC] font-bold text-base">{{ finalWinner ? displayName(finalWinner) : '—' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const users = ref([])
const slots = reactive([
  { label: 'A', mode: 'name', userId: null, name: '' },
  { label: 'B', mode: 'name', userId: null, name: '' },
  { label: 'C', mode: 'name', userId: null, name: '' },
])

const firstRoundMatches = [
  { id: 'ab', left: 'A', right: 'B' },
  { id: 'bc', left: 'B', right: 'C' },
  { id: 'ca', left: 'C', right: 'A' },
]

const results = reactive({ ab: null, bc: null, ca: null })
const secondRoundWinner = ref(null)
const finalWinner = ref(null)

const displayName = (slotKey) => {
  const slot = slots.find((s) => s.label === slotKey)
  if (!slot) return slotKey

  if (slot.mode === 'profile' && slot.userId) {
    const selected = users.value.find((u) => u.id === slot.userId)
    if (selected) return `${selected.name} (@${selected.username})`
  }

  return slot.name || `Soutěžící ${slot.label}`
}

const ranking = computed(() => {
  const score = { A: 0, B: 0, C: 0 }
  firstRoundMatches.forEach((match) => {
    const winner = results[match.id]
    if (winner && score[winner] !== undefined) score[winner] += 1
  })

  return Object.entries(score)
    .map(([key, wins]) => ({ key, wins }))
    .sort((a, b) => b.wins - a.wins || a.key.localeCompare(b.key))
})

const finalistFromFirst = computed(() => ranking.value[0]?.key || null)
const thirdVsSecond = computed(() => ({
  left: ranking.value[2]?.key || null,
  right: ranking.value[1]?.key || null,
}))

onMounted(async () => {
  try {
    const response = await axios.get('/api/users')
    users.value = Array.isArray(response.data) ? response.data : []
  } catch {
    users.value = []
  }
})
</script>