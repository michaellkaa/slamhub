<template>
  <div class="min-h-screen bg-app text-app px-3 sm:px-4 md:px-6 py-6 md:py-10 pb-28 lg:pb-10">
    <div class="w-full max-w-5xl mx-auto">
      <div class="mb-6 md:mb-8">
        <button type="button" @click="goBack" class="text-sm text-app-muted hover:text-app transition mb-3">
          ← Zpět do nastavení
        </button>
        <h1 class="text-xl sm:text-2xl font-semibold">Organizátor dashboard</h1>
        <p class="text-app-muted text-sm mt-1">Vyber svůj event a uprav jeho ligovou tabulku.</p>
      </div>

      <div v-if="loadingEvents" class="rounded-xl border border-app bg-surface p-6 text-sm text-app-muted">
        Načítám tvoje eventy…
      </div>

      <div v-else class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        <div class="space-y-4">
          <div class="rounded-2xl border border-app bg-surface p-4">
            <div class="flex flex-col gap-3">
              <div>
                <h2 class="text-sm font-semibold mb-3">Moje eventy</h2>
                <input
                  v-model="searchQuery"
                  type="search"
                  placeholder="Hledat event podle názvu nebo místa"
                  class="w-full rounded-2xl border border-app bg-app px-3 py-2 text-sm text-app focus:outline-none focus:border-pink-500 transition"
                />
              </div>
            </div>
            <div v-if="filteredEvents.length" class="space-y-3">
              <button
                v-for="event in filteredEvents"
                :key="event.id"
                type="button"
                @click="selectEvent(event.id)"
                :class="selectedEventId === event.id ? 'bg-pink-500 text-white' : 'bg-app hover:bg-surface-hover text-app'"
                class="w-full text-left rounded-2xl border border-app p-3 transition"
              >
                <div class="font-semibold truncate">{{ event.title || 'Bez názvu' }}</div>
                <div class="text-xs text-app-muted mt-1">
                  {{ event.event_mode === 'league' ? 'Liga' : 'Exhibice' }} · {{ formatDate(event.starts_at) }}
                </div>
              </button>

              <div v-if="!searchQuery && events.length > filteredEvents.length" class="mt-2 text-right">
                <button @click="showAllEvents = true" class="text-sm text-pink-500 hover:underline">Zobrazit všechny moje eventy</button>
              </div>
              <div v-if="!searchQuery && showAllEvents && events.length > 4" class="mt-2 text-right">
                <button @click="showAllEvents = false" class="text-sm text-app-muted hover:underline">Skrýt seznam</button>
              </div>
            </div>
            <div v-else class="text-sm text-app-muted">
              {{ searchQuery ? 'Nebyly nalezeny žádné eventy podle této fráze.' : 'Nemáš zatím žádné eventy.' }}
            </div>
          </div>

          <div class="rounded-2xl border border-app bg-surface p-4">
            <h2 class="text-sm font-semibold mb-3">Nápověda</h2>
            <p class="text-sm text-app-muted">
              Zde můžeš vybrat svůj event a upravit ligovou tabulku. Pokud použiješ body z papíru, můžeš je přepsat sem ručně.
            </p>
          </div>
        </div>

        <div class="rounded-2xl border border-app bg-surface p-4">
          <div v-if="!selectedEvent">
            <div class="text-sm text-app-muted">Vyber event vlevo pro zobrazení ligy a ruční úpravy.</div>
          </div>

          <div v-else>
            <div class="flex flex-col gap-3 mb-4">
              <div class="text-xs text-app-muted uppercase">Aktuální event</div>
              <div class="text-lg font-semibold">{{ selectedEvent.title || 'Bez názvu' }}</div>
              <div class="text-sm text-app-muted">
                {{ selectedEvent.location || 'Žádné místo' }} · {{ selectedEvent.event_mode === 'league' ? 'Liga' : 'Exhibice' }}
              </div>
            </div>

            <div v-if="selectedEvent.event_mode !== 'league'" class="rounded-2xl border border-app bg-app p-4 text-sm text-app-muted">
              Tento event není nastaven jako liga. Ligové výsledky můžeš upravovat pouze pro eventy s módem <strong>league</strong>.
            </div>

            <div v-else class="space-y-6">
              <div>
                <div class="text-sm text-app-muted mb-3">Složení slotů</div>
                <div class="grid gap-3 md:grid-cols-2">
                  <div v-for="slot in localSlots" :key="slot.id" class="rounded-2xl border border-app bg-app p-3">
                    <div class="text-xs text-app-muted mb-1">Slot {{ slot.id }}</div>
                    <select v-model="slot.value" class="w-full rounded-xl bg-surface p-2 text-sm">
                      <option :value="null">Vyber účastníka</option>
                      <option v-for="option in allParticipants" :key="option.key" :value="option.label">{{ option.label }}</option>
                    </select>
                  </div>
                </div>
              </div>

              <div>
                <div class="text-sm text-app-muted mb-3">Výsledky zápasů</div>
                <div class="grid gap-3">
                  <div v-for="match in matches" :key="match.id" class="rounded-2xl border border-app bg-app p-3">
                    <div class="flex items-center justify-between gap-3 mb-2">
                      <div class="text-sm font-medium">{{ getSlot(match.left) }} vs {{ getSlot(match.right) }}</div>
                      <span class="text-xs text-app-muted">{{ match.label }}</span>
                    </div>
                    <select v-model="roundRobin[match.id]" class="w-full rounded-xl bg-surface p-2 text-sm">
                      <option :value="null">Vyber vítěze</option>
                      <option :value="getSlot(match.left)">{{ getSlot(match.left) }}</option>
                      <option :value="getSlot(match.right)">{{ getSlot(match.right) }}</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-app bg-app p-3">
                <div class="text-sm text-app-muted mb-3">Finálová tabulka</div>
                <div class="grid gap-3">
                  <div class="rounded-2xl bg-surface p-3">
                    <div class="text-xs text-app-muted mb-1">2. kolo: 3. místo vs 2. místo</div>
                    <select v-model="secondRoundWinner" class="w-full rounded-xl bg-surface p-2 text-sm">
                      <option :value="null">Vyber vítěze 2. kola</option>
                      <option v-if="ranking[2]" :value="ranking[2]">{{ ranking[2] }}</option>
                      <option v-if="ranking[1]" :value="ranking[1]">{{ ranking[1] }}</option>
                    </select>
                  </div>
                  <div class="rounded-2xl bg-surface p-3">
                    <div class="text-xs text-app-muted mb-1">Finále: 1. místo vs vítěz 2. kola</div>
                    <select v-model="finalWinner" class="w-full rounded-xl bg-surface p-2 text-sm">
                      <option :value="null">Vyber vítěze ligy</option>
                      <option v-if="ranking[0]" :value="ranking[0]">{{ ranking[0] }}</option>
                      <option v-if="secondRoundWinner" :value="secondRoundWinner">{{ secondRoundWinner }}</option>
                    </select>
                  </div>
                </div>
              </div>

              <button @click="saveLeague" class="w-full rounded-2xl bg-pink-500 hover:bg-pink-600 py-3 text-white font-semibold">
                Uložit ligu
              </button>

              <div v-if="saveMessage" class="text-sm text-emerald-300">{{ saveMessage }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const events = ref([])
const searchQuery = ref('')
const showAllEvents = ref(false)
const loadingEvents = ref(true)
const selectedEventId = ref(null)
const selectedEvent = computed(() => events.value.find((event) => event.id === selectedEventId.value) || null)
const localSlots = ref([{ id: 'A', value: null }, { id: 'B', value: null }, { id: 'C', value: null }])
const roundRobin = ref({ ab: null, bc: null, ca: null })
const secondRoundWinner = ref(null)
const finalWinner = ref(null)
const eventPerformers = ref([])
const guestPerformers = ref([])
const saveMessage = ref('')

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
