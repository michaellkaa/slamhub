<template>
  <div class="w-full flex justify-center mt-8 px-4 pb-28 md:pb-8">
    <div class="w-full max-w-4xl">
      <div class="mb-3 px-1 flex items-center justify-between text-sm">
        <span class="text-white/60">Leaderboard</span>
        <span class="text-white/60">Awards</span>
      </div>

      <div v-if="loading" class="text-white/60 py-6">Načítám leaderboard…</div>
      <div v-else-if="error" class="text-red-300 py-6">{{ error }}</div>
      <div v-else-if="rows.length === 0" class="text-white/60 py-6">Zatím žádní performeři.</div>

      <div v-else class="flex flex-col items-center gap-3">
        <LeaderboardRow
          v-for="row in rows"
          :key="row.id"
          :row="row"
          @select="goToProfile"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import LeaderboardRow from './LeaderboardRow.vue'

const router = useRouter()
const rows = ref([])
const loading = ref(false)
const error = ref('')

const fetchLeaderboard = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get('/api/awards/leaderboard')
    rows.value = Array.isArray(response.data) ? response.data : []
  } catch (err) {
    error.value = err?.response?.data?.message || 'Nepodařilo se načíst leaderboard.'
  } finally {
    loading.value = false
  }
}

const goToProfile = (row) => {
  if (!row?.username) return
  router.push(`/profile/${row.username}`)
}

onMounted(fetchLeaderboard)
</script>