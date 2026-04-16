<template>
  <div class="rounded-2xl bg-[#141418] border border-[#1f1f22] p-4">
    <div class="text-sm text-white/60 mb-4">League flow</div>

    <div v-if="rows.length" class="space-y-3">

      <div
        v-for="row in rows"
        :key="row.event_id"
        class="rounded-xl bg-[#0f0f12] border border-[#1f1f22] p-3"
      >

        <!-- HEADER -->
        <div class="flex justify-between items-start">
          <div>
            <div class="text-xs text-white/50">League event</div>
            <div class="font-semibold text-white">
              {{ row.event_title }}
            </div>
          </div>

          <div class="text-right">
            <div class="text-xs text-white/50">Winner</div>
            <div class="text-[#FFF7CC] font-bold">
              {{ row.winner || '—' }}
            </div>
          </div>
        </div>

        <!-- PROGRESS -->
        <div class="mt-3 cursor-pointer" @click="toggle(row.event_id)">
          <div class="h-2 bg-white/10 rounded overflow-hidden">
            <div
              class="h-full bg-pink-500 transition-all"
              :style="{ width: (row.progress || 0) + '%' }"
            />
          </div>

          <div class="text-xs text-white/50 mt-1">
            Progress: {{ row.progress || 0 }}% (click to view spider)
          </div>
        </div>

        <!-- SPIDER -->
        <div
          v-if="openId === row.event_id"
          class="mt-4 border-t border-white/10 pt-3 space-y-2"
        >

          <div class="text-xs text-white/50 mb-2">
            Spider breakdown
          </div>

          <div
            v-for="m in getSpider(row)"
            :key="m.id"
            class="bg-[#141418] border border-[#1f1f22] rounded px-3 py-2 text-sm"
          >

            <div class="flex justify-between text-white/40 items-center">

              <span :class="{ 'text-pink-400 font-bold': m.winner === m.left }">
                {{ m.left }}
              </span>

              <span class="text-white/40">vs</span>

              <span :class="{ 'text-pink-400 font-bold': m.winner === m.right }">
                {{ m.right }}
              </span>

            </div>

            <div class="text-xs text-white/50 mt-1">
              Winner:
              <span class="text-[#FFF7CC] font-bold">
                {{ m.winner || '—' }}
              </span>
            </div>

          </div>

        </div>

      </div>

    </div>

    <div v-else class="text-sm text-white/40 text-center">
      No league events yet
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const rows = ref([])
const openId = ref(null)



const getSpider = (row) => {
  const data = leagueCache.value[row.event_id] || {}

  const slots = data.slots || []
  const rr = data.round_robin || {}

  const getName = (id) => {
    const slot = slots.find(s => s.id === id)
    return slot?.performer_name || slot?.value || `Slot ${id}`
  }

  const matches = [
    { id: 'ab', left: 'A', right: 'B' },
    { id: 'bc', left: 'B', right: 'C' },
    { id: 'ca', left: 'C', right: 'A' }
  ]

  return matches.map(m => ({
    id: m.id,
    left: getName(m.left),
    right: getName(m.right),
    winner: rr?.[m.id] || null
  }))
}

const leagueCache = ref({})
const loadingSpider = ref({})

const fetchLeagueDetail = async (eventId) => {
  if (leagueCache.value[eventId]) return leagueCache.value[eventId]

  loadingSpider.value[eventId] = true

  try {
    const res = await axios.get(`/api/events/${eventId}/league`)
    leagueCache.value[eventId] = res.data?.league_data || {}
    return leagueCache.value[eventId]
  } catch (e) {
    console.error('Failed to load league detail', e)
    return {}
  } finally {
    loadingSpider.value[eventId] = false
  }
}

const toggle = async (id) => {
  if (openId.value === id) {
    openId.value = null
    return
  }

  openId.value = id
  await fetchLeagueDetail(id)
}

onMounted(async () => {
  const res = await axios.get('/api/awards/league-progress')
  console.log('API RESPONSE:', res.data)

  rows.value = Array.isArray(res.data) ? res.data : []
})
</script>