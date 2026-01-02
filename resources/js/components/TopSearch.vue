<template>
  <div class="relative w-full max-w-xl">
    <input
      v-model="query"
      @input="search"
      placeholder="Hledat…"
      class="w-full p-3 rounded-xl bg-[#1d1d21] text-white focus:outline-none"
    />

    <div
      v-if="query && results"
      class="absolute mt-2 w-full bg-[#141418] rounded-xl shadow-xl p-4 z-50"
    >
      <div v-if="results.events.length">
        <p class="text-xs text-white/50 mb-1">Eventy</p>
        <div v-for="e in results.events" :key="e.id">{{ e.title }}</div>
      </div>

      <div v-if="results.users.length" class="mt-3">
        <p class="text-xs text-white/50 mb-1">Lidé</p>
        <div v-for="u in results.users" :key="u.id">{{ u.name }}</div>
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
import axios from 'axios'
import debounce from 'lodash/debounce'

const query = ref('')
const results = ref(null)

const search = debounce(async () => {
  if (!query.value) {
    results.value = null
    return
  }

  try {
    const res = await axios.get('/api/search', {
      params: { q: query.value }
    })

    results.value = res.data
  } catch (err) {
    console.error('Search failed:', err.response?.data)
    results.value = null
  }
}, 300)

</script>
