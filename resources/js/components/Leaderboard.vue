<template>
  <div class="w-full flex justify-center mt-10 px-4 pb-24">
    <div class="w-full max-w-3xl">


      <div v-if="loading" class="text-white/60 py-6 text-center">
        <LeaderboardRow v-for="i in 6" :key="i" loading />      
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