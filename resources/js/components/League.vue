<!--<template>
  <div class="w-full px-4 pb-28 md:pb-8 mt-4">
    <div class="mx-auto w-full max-w-3xl space-y-3">
      <div class="rounded-2xl bg-[#141418] border border-[#1f1f22] p-4">
        <div class="text-sm text-white/60 mb-2">Default spider (3 performers)</div>
        <div class="grid grid-cols-3 gap-2 text-center text-sm">
          <div class="rounded-lg bg-[#0f0f12] border border-[#1f1f22] py-2">Performer A</div>
          <div class="rounded-lg bg-[#0f0f12] border border-[#1f1f22] py-2">Performer B</div>
          <div class="rounded-lg bg-[#0f0f12] border border-[#1f1f22] py-2">Performer C</div>
        </div>
        <div class="mt-3 text-xs text-white/60">
          Round robin: A vs B, B vs C, C vs A -> 3rd vs 2nd -> final vs 1st
        </div>
      </div>

      <div
        v-for="row in rows"
        :key="row.event_id"
        class="rounded-2xl bg-[#141418] border border-[#1f1f22] p-4"
      >
        <div class="flex items-center justify-between gap-3">
          <div>
            <div class="text-sm text-white/60">League event</div>
            <div class="font-semibold">{{ row.event_title }}</div>
          </div>
          <div class="text-sm text-right">
            <div class="text-white/60">Vitez</div>
            <div class="font-bold text-[#FFF7CC]">{{ row.winner || '—' }}</div>
          </div>
        </div>
        <div class="mt-3 h-2 rounded bg-white/10 overflow-hidden">
          <div class="h-full bg-pink-500" :style="{ width: `${row.progress}%` }"></div>
        </div>
        <div class="mt-1 text-xs text-white/60">Progress: {{ row.progress }}%</div>
      </div>
    </div>
  </div>
</template>-->
<template>
  
<div class="rounded-2xl bg-[#141418] border border-[#1f1f22] p-4">
  <div class="text-sm text-white/60 mb-4">League flow (3 performers)</div>

  <!-- ROUND ROBIN -->
  <div class="space-y-2 mb-4">
    <div class="text-xs text-white/50">Round robin</div>
    
    <div class="flex justify-between bg-[#0f0f12] border border-[#1f1f22] rounded px-3 py-2 text-sm">
      <span>A</span>
      <span class="text-white/40">vs</span>
      <span>B</span>
    </div>

    <div class="flex justify-between bg-[#0f0f12] border border-[#1f1f22] rounded px-3 py-2 text-sm">
      <span>B</span>
      <span class="text-white/40">vs</span>
      <span>C</span>
    </div>

    <div class="flex justify-between bg-[#0f0f12] border border-[#1f1f22] rounded px-3 py-2 text-sm">
      <span>C</span>
      <span class="text-white/40">vs</span>
      <span>A</span>
    </div>
  </div>

  <!-- ARROW -->
  <div class="text-center text-white/40 mb-4">↓ výsledky ↓</div>

  <!-- PLAY-IN -->
  <div class="space-y-2 mb-4">
    <div class="text-xs text-white/50">Play-in (2. vs 3.)</div>

    <div class="flex justify-between bg-[#0f0f12] border border-[#1f1f22] rounded px-3 py-2 text-sm">
      <span>2. místo</span>
      <span class="text-white/40">vs</span>
      <span>3. místo</span>
    </div>
  </div>

  <!-- FINAL -->
  <div class="space-y-2">
    <div class="text-xs text-white/50">Finále</div>

    <div class="flex justify-between bg-[#0f0f12] border border-[#1f1f22] rounded px-3 py-2 text-sm">
      <span>1. místo</span>
      <span class="text-white/40">vs</span>
      <span>Vítěz play-in</span>
    </div>
  </div>
</div>
</template>
<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const rows = ref([])

onMounted(async () => {
  const res = await axios.get('/api/awards/league-progress')
  rows.value = Array.isArray(res.data) ? res.data : []
})
</script>