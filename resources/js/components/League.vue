<template>
  <div class="w-full px-4 pb-28 md:pb-8 mt-4">
    <div class="mx-auto w-full max-w-3xl space-y-3">
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