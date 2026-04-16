<template>
  <div class="rounded-2xl bg-[#141418] border border-[#1f1f22] p-5 space-y-4">

    <div v-if="rows.length" class="space-y-4">

      <div
        v-for="row in rows"
        :key="row.event_id"
        class="border border-[#1f1f22] rounded-xl bg-[#0f0f12] overflow-hidden"
      >

        <div class="flex justify-between items-center px-4 py-3 border-b border-white/5">

          <div>
            <div class="text-xs text-white/40">Event</div>
            <div class="text-white font-medium">
              {{ row.event_title }}
            </div>
          </div>

          <div class="text-right">
            <div class="text-xs text-white/40">Winner</div>
            <div class="text-[#FFF7CC] font-semibold">
              {{ row.winner || '—' }}
            </div>
          </div>

        </div>

        <div class="px-4 py-3 cursor-pointer" @click="toggle(row.event_id)">

          <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
            <div
              class="h-full bg-pink-500 transition-all"
              :style="{ width: (row.progress || 0) + '%' }"
            />
          </div>

          <div class="text-xs text-white/40 mt-2 flex justify-between">
            <span>Progress</span>
            <span>{{ row.progress || 0 }}%</span>
          </div>

        </div>

        <div
          v-if="openId === row.event_id"
          class="border-t border-white/5 px-4 py-4 space-y-3"
        >

          <div class="grid gap-2">

            <div
              v-for="m in getSpider(row)"
              :key="m.id"
              class="hidden lg:flex items-center justify-between px-3 py-2 rounded-lg bg-[#141418] border border-[#1f1f22]"
            >

              <div class="flex items-center gap-3">

                <span :class="m.winner === m.left ? 'text-pink-400 font-medium' : 'text-white/60'">
                  {{ m.left }}
                </span>

                <span class="text-white/30 text-xs">vs</span>

                <span :class="m.winner === m.right ? 'text-pink-400 font-medium' : 'text-white/60'">
                  {{ m.right }}
                </span>

              </div>

              <div class="text-xs text-[#FFF7CC] font-semibold">
                {{ m.winner || '—' }}
              </div>

            </div>
            <div
            v-for="m in getSpider(row)"
            :key="m.id"
            class="lg:hidden flex flex-col gap-2 md:flex-row md:items-center md:justify-between px-3 py-3 rounded-lg bg-[#141418] border border-[#1f1f22]"
            >

            <!-- MATCH ROW -->
            <div class="flex items-center justify-between md:justify-start gap-2 w-full md:w-auto">

                <span
                class="text-sm truncate max-w-[40%]"
                :class="m.winner === m.left ? 'text-pink-400 font-medium' : 'text-white/60'"
                >
                {{ m.left }}
                </span>

                <span class="text-white/30 text-xs shrink-0 px-2">
                vs
                </span>

                <span
                class="text-sm truncate max-w-[40%]"
                :class="m.winner === m.right ? 'text-pink-400 font-medium' : 'text-white/60'"
                >
                {{ m.right }}
                </span>

            </div>

            <!-- WINNER -->
            <div class="text-xs text-[#FFF7CC] font-semibold md:text-right shrink-0">
                {{ m.winner || '—' }}
            </div>

            </div>
          </div>

        </div>

      </div>

    </div>

    <div v-else class="text-sm text-white/40 text-center py-6">
      No league events yet
    </div>

  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'

const rows = ref([])
const openId = ref(null)

const leagueCache = ref({})
const loadingSpider = ref({})
let interval = null


const fetchData = async () => {
  try {
    const res = await axios.get('/api/awards/league-progress', {
      headers: {
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache'
      },
      params: {
        t: Date.now()
      }
    })

    rows.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('Failed to load league progress', e)
  }
}


const getSpider = (row) => {
  const data = leagueCache.value[row.event_id] || {}

  const slots = data.slots || []
  const rr = data.round_robin || {}

  const getName = (id) => {
    const slot = slots.find(s => s.id === id)
    return slot?.performer_name || slot?.value || `${id}`
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


const fetchLeagueDetail = async (eventId) => {
  if (leagueCache.value[eventId]) return leagueCache.value[eventId]

  loadingSpider.value[eventId] = true

  try {
    const res = await axios.get(`/api/events/${eventId}/league`, {
      params: { t: Date.now() }
    })

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
  await fetchData()

  interval = setInterval(fetchData, 5000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>
