<template>
  <div class="relative w-full max-w-xl">
    <div class="flex gap-2">
      <input v-model="query" @input="search" placeholder="Hledat…"
        class="flex-1 p-3 rounded-xl bg-[#1d1d21] text-white focus:outline-none" />
    </div>

    <div v-if="query && results" class="absolute mt-2 w-full bg-[#141418] rounded-xl shadow-xl p-4 z-50">
      <div v-if="results.users.length">
        <p class="text-xs text-white/50 mb-2">Lidé</p>

        <div v-for="u in results.users" :key="u.username" @click="goToProfile(u.username)"
          class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer">
          <img :src="u.profile_pic || '/images/default-avatar.png'" class="w-9 h-9 rounded-full object-cover" />

          <div>
            <p class="text-white">{{ u.name }}</p>
            <p class="text-xs text-white/40">{{ u.role }}</p>
          </div>
        </div>
      </div>

      <div v-if="results.events.length" class="mt-3">
        <p class="text-xs text-white/50 mb-2">Eventy</p>
        <div v-for="e in results.events" :key="e.id" @click="goToEvent(e.id)"
          class="text-white p-2 rounded hover:bg-white/5 cursor-pointer">
          {{ e.title }}
        </div>
      </div>

      <div v-if="!results.users.length && !results.events.length" class="text-center text-white/40 py-3">
        Žádné výsledky
      </div>

      <!--<div v-if="results.posts.length" class="mt-3">
        <p class="text-xs text-white/50 mb-1">Příspěvky</p>
        <div v-for="p in results.posts" :key="p.id">{{ p.title }}</div>
      </div>-->
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import debounce from 'lodash/debounce'

const router = useRouter()

const query = ref('')
const role = ref('all')
const results = ref(null)

const search = debounce(async () => {
  if (!query.value || query.value.length < 2) {
    results.value = null
    return
  }

  try {
    const res = await axios.get('/api/search', {
      params: {
        q: query.value,
        role: role.value !== 'all' ? role.value : null
      }
    })

    results.value = res.data
  } catch (err) {
    console.error('Search failed:', err.response?.data)
    results.value = null
  }
}, 300)

const goToProfile = (username) => {
  results.value = null
  query.value = ''
  router.push(`/profile/${username}`)
}


const goToEvent = (id) => {
  results.value = null
  query.value = ''
  router.push({ name: 'EventDetail', params: { id } })
}

</script>