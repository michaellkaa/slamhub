<template>
  <div class="min-h-screen bg-app text-app flex justify-center px-3 md:px-4 py-6 md:py-10 pb-28 lg:pb-10">
    <div class="w-full max-w-5xl">
      <button type="button" @click="goBack" class="mb-6 text-sm text-app-muted hover:text-app transition">
        ← Zpět do nastavení
      </button>

      <div v-if="loadingEvents" class="rounded-xl border border-app bg-surface p-6 text-sm text-app-muted">
        Načítám tvoje eventy…
      </div>

      <div v-else class="flex flex-col md:flex-row gap-6 md:gap-10">
        <aside class="md:w-56 shrink-0">
          <EventList
            :searchQuery="searchQuery"
            :events="events"
            :filteredEvents="filteredEvents"
            :selectedEventId="selectedEventId"
            :showAllEvents="showAllEvents"
            :activeTab="activeTab"
            :formatDate="formatDate"
            @select="selectEvent"
            @update:searchQuery="val => searchQuery = val"
            @update:showAllEvents="val => showAllEvents = val"
            @update:activeTab="val => activeTab = val"
          />
        </aside>

        <div class="flex-1 min-w-0">
          <div v-if="!selectedEvent" class="text-sm text-app-muted">
            Vyber event vlevo — výběr platí pro všechny taby.
          </div>

          <template v-else>
            <template v-if="activeTab === 'league'">
              <h1 class="text-xl font-semibold">Liga</h1>
              <p class="text-app-muted text-sm mt-1">
                {{ selectedEvent.title || 'Bez názvu' }} · {{ selectedEvent.location || 'Bez místa' }}
              </p>

              <div v-if="selectedEvent.event_mode !== 'league'" class="mt-6 rounded-xl border border-app bg-surface p-4 text-sm text-app-muted">
                Tento event není nastaven jako liga. Ligové výsledky můžeš upravovat jen pro eventy s módem <strong>league</strong>.
              </div>

              <div v-else class="mt-6 space-y-6">
                <div>
                  <div class="text-sm text-app-muted mb-3">Složení slotů</div>
                  <div class="grid gap-3 md:grid-cols-2">
                    <div v-for="slot in localSlots" :key="slot.id" class="rounded-xl border border-app bg-surface p-3">
                      <div class="text-xs text-app-muted mb-1">Slot {{ slot.id }}</div>
                      <select v-model="slot.value" class="w-full rounded-md bg-app border border-app p-2 text-sm">
                        <option :value="null">Vyber účastníka</option>
                        <option v-for="option in allParticipants" :key="option.key" :value="option.label">{{ option.label }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div>
                  <div class="text-sm text-app-muted mb-3">Výsledky zápasů</div>
                  <div class="grid gap-3">
                    <div v-for="match in matches" :key="match.id" class="rounded-xl border border-app bg-surface p-3">
                      <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="text-sm font-medium">{{ getSlot(match.left) }} vs {{ getSlot(match.right) }}</div>
                        <span class="text-xs text-app-muted">{{ match.label }}</span>
                      </div>
                      <select v-model="roundRobin[match.id]" class="w-full rounded-md bg-app border border-app p-2 text-sm">
                        <option :value="null">Vyber vítěze</option>
                        <option :value="getSlot(match.left)">{{ getSlot(match.left) }}</option>
                        <option :value="getSlot(match.right)">{{ getSlot(match.right) }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="rounded-xl border border-app bg-surface p-3">
                  <div class="text-sm text-app-muted mb-3">Finálová tabulka</div>
                  <div class="grid gap-3">
                    <div class="rounded-lg bg-app p-3">
                      <div class="text-xs text-app-muted mb-1">2. kolo: 3. místo vs 2. místo</div>
                      <select v-model="secondRoundWinner" class="w-full rounded-md bg-surface border border-app p-2 text-sm">
                        <option :value="null">Vyber vítěze 2. kola</option>
                        <option v-if="ranking[2]" :value="ranking[2]">{{ ranking[2] }}</option>
                        <option v-if="ranking[1]" :value="ranking[1]">{{ ranking[1] }}</option>
                      </select>
                    </div>
                    <div class="rounded-lg bg-app p-3">
                      <div class="text-xs text-app-muted mb-1">Finále: 1. místo vs vítěz 2. kola</div>
                      <select v-model="finalWinner" class="w-full rounded-md bg-surface border border-app p-2 text-sm">
                        <option :value="null">Vyber vítěze ligy</option>
                        <option v-if="ranking[0]" :value="ranking[0]">{{ ranking[0] }}</option>
                        <option v-if="secondRoundWinner" :value="secondRoundWinner">{{ secondRoundWinner }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <button
                  type="button"
                  @click="saveLeague"
                  class="w-full rounded-md bg-pink-500 hover:bg-pink-600 py-3 text-white font-semibold transition"
                >
                  Uložit ligu
                </button>
                <p v-if="saveMessage" class="text-sm text-emerald-500">{{ saveMessage }}</p>
              </div>
            </template>

            <template v-else-if="activeTab === 'manual'">
              <h1 class="text-xl font-semibold">Manuální bodování</h1>
              <p class="text-app-muted text-sm mt-1">
                {{ selectedEvent.title || 'Bez názvu' }} — vlož body z papíru a ulož.
              </p>
              <div class="mt-6">
                <PointsEditor
                  :rawPoints="rawPoints"
                  :parsedParticipants="parsedParticipants"
                  @parse="parsePoints"
                  @apply="applyParsedToSlots"
                  @save="saveLeague"
                  @update:rawPoints="val => rawPoints = val"
                  @update:parsedParticipant="({ idx, points }) => { parsedParticipants[idx].points = points }"
                />
              </div>
            </template>

            <template v-else-if="activeTab === 'voting'">
              <VotingHostPanel :event-id="selectedEvent.id" />
            </template>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import EventList from '../components/Organizer/EventList.vue'
import PointsEditor from '../components/Organizer/PointsEditor.vue'
import VotingHostPanel from '../components/Organizer/VotingHostPanel.vue'

const router = useRouter()
const events = ref([])
const searchQuery = ref('')
const showAllEvents = ref(false)
const loadingEvents = ref(true)
const selectedEventId = ref(null)
const activeTab = ref('league')
const selectedEvent = computed(() => events.value.find((event) => event.id === selectedEventId.value) || null)
const localSlots = ref([{ id: 'A', value: null }, { id: 'B', value: null }, { id: 'C', value: null }])
const roundRobin = ref({ ab: null, bc: null, ca: null })
const secondRoundWinner = ref(null)
const finalWinner = ref(null)
const eventPerformers = ref([])
const guestPerformers = ref([])
const saveMessage = ref('')
const rawPoints = ref('')
const parsedParticipants = ref([])
const pointsMap = ref({})

const matches = [
  { id: 'ab', left: 'A', right: 'B', label: 'Zápas A vs B' },
  { id: 'bc', left: 'B', right: 'C', label: 'Zápas B vs C' },
  { id: 'ca', left: 'C', right: 'A', label: 'Zápas C vs A' },
]

const allParticipants = computed(() => {
  const fromEvent = eventPerformers.value.map((p) => ({ key: `u-${p.id}`, label: `${p.name} (@${p.username})` }))
  const fromGuests = guestPerformers.value.map((g, idx) => ({ key: `g-${idx}`, label: g }))
  return [...fromEvent, ...fromGuests]
})

const filteredEvents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) {
    if (showAllEvents.value) return events.value
    return events.value.slice(0, 4)
  }
  return events.value.filter((event) => {
    const title = (event.title || '').toLowerCase()
    const location = (event.location || '').toLowerCase()
    return title.includes(query) || location.includes(query)
  })
})

const formatDate = (value) => {
  if (!value) return 'Neznámé'
  return new Date(value).toLocaleString('cs-CZ', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const selectEvent = async (id) => {
  if (selectedEventId.value === id) return
  selectedEventId.value = id
  saveMessage.value = ''
  await loadLeagueData(id)
}

const getSlot = (id) => localSlots.value.find((s) => s.id === id)?.value || `Soutěžící ${id}`

const ranking = computed(() => {
  const wins = {}
  localSlots.value.forEach((s) => { if (s.value) wins[s.value] = 0 })
  matches.forEach((m) => {
    const winner = roundRobin.value[m.id]
    if (winner && wins[winner] !== undefined) wins[winner] += 1
  })
  return Object.entries(wins).sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0])).map(([name]) => name)
})

const parsePoints = () => {
  const text = rawPoints.value || ''
  const lines = text.split(/\n|,/).map(s => s.trim()).filter(Boolean)
  const out = []
  for (const line of lines) {
    const m = line.match(/^(.*?)[\s,:-]*([0-9]+)\s*$/)
    if (m) {
      out.push({ name: m[1].trim(), points: Number(m[2]) })
    } else {
      out.push({ name: line, points: 0 })
    }
  }
  parsedParticipants.value = out
  pointsMap.value = out.reduce((acc, p) => ({ ...acc, [p.name]: p.points }), {})
}

const applyParsedToSlots = () => {
  if (!parsedParticipants.value.length) return
  for (let i = 0; i < localSlots.value.length; i++) {
    localSlots.value[i].value = parsedParticipants.value[i]?.name || localSlots.value[i].value
  }
}

const loadEvents = async () => {
  loadingEvents.value = true
  try {
    const res = await axios.get('/api/profile/events')
    events.value = Array.isArray(res.data) ? res.data : []
    if (events.value.length && !selectedEventId.value) {
      selectedEventId.value = events.value[0].id
      await loadLeagueData(selectedEventId.value)
    }
  } catch (err) {
    console.error('Chyba načítání eventů:', err)
  } finally {
    loadingEvents.value = false
  }
}

const loadLeagueData = async (eventId) => {
  try {
    const res = await axios.get(`/api/events/${eventId}/league`)
    const data = res.data.league_data || {}
    eventPerformers.value = res.data.event_performers || []
    guestPerformers.value = res.data.guest_performers || []
    if (Array.isArray(data.slots)) {
      localSlots.value = data.slots.map((s) => ({ id: s.id, value: s.performer_name || null }))
    } else {
      localSlots.value = [{ id: 'A', value: null }, { id: 'B', value: null }, { id: 'C', value: null }]
    }
    roundRobin.value = { ab: null, bc: null, ca: null, ...(data.round_robin || {}) }
    secondRoundWinner.value = data.second_round_winner || null
    finalWinner.value = data.final_winner || null
    if (data.points && typeof data.points === 'object') {
      parsedParticipants.value = Object.entries(data.points).map(([name, pts]) => ({ name, points: pts }))
      pointsMap.value = { ...data.points }
    } else {
      parsedParticipants.value = []
      pointsMap.value = {}
    }
  } catch (err) {
    console.error('Chyba načítání ligy:', err)
  }
}

const saveLeague = async () => {
  if (!selectedEvent.value) return
  saveMessage.value = ''
  const payload = {
    slots: localSlots.value.map((s) => ({ id: s.id, performer_id: null, performer_name: s.value || null })),
    round_robin: roundRobin.value,
    second_round_winner: secondRoundWinner.value,
    final_winner: finalWinner.value,
  }
  if (parsedParticipants.value.length) {
    payload.points = parsedParticipants.value.reduce((acc, p) => ({ ...acc, [p.name]: p.points }), {})
  } else if (Object.keys(pointsMap.value).length) {
    payload.points = pointsMap.value
  }

  try {
    await axios.put(`/api/events/${selectedEvent.value.id}/league`, { league_data: payload })
    saveMessage.value = 'Liga byla uložena.'
  } catch (err) {
    console.error('Chyba ukládání ligy:', err)
    saveMessage.value = 'Ukládání selhalo, zkontroluj data.'
  }
}

const goBack = () => router.push('/settings')

onMounted(loadEvents)
</script>
