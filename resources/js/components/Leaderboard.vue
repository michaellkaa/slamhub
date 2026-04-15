<template>
  <div class="w-full flex justify-center mt-8 px-4 md:px-6 pb-24">
    <div class="w-full max-w-3xl">
      <div v-if="loading" class="flex flex-col gap-3 py-6">
        <LeaderboardRow v-for="i in 6" :key="i" loading />      
      </div>

      <div v-else-if="error" class="py-8 text-center text-red-200">
        {{ error }}
      </div>

      <div v-else-if="!rows.length" class="py-8 text-center text-white/60">
        Leaderboard je zatim prazdny.
      </div>

      <div v-else class="flex flex-col gap-3">
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