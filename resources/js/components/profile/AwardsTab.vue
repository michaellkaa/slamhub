<template>
  <div class="mt-8 w-[70%] space-y-4">
    <div v-if="awards.length > 0">
      <div
        v-for="award in awards"
        :key="award.id"
        class="flex items-center gap-4 p-4 bg-[#1d1d21] rounded-xl mb-4"
      >
        <img
          :src="award.icon_url"
          class="w-10 h-10 object-contain"
        />
        <div>
          <div class="font-medium text-white">
            {{ award.title }}
          </div>
          <div class="text-sm text-white/40">
            {{ award.year }}
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-white/20 text-center py-8">
      žádná oocenění nenalezena...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

axios.defaults.withCredentials = true

const awards = ref([])

import { useRoute } from 'vue-router'

const route = useRoute()

onMounted(async () => {
  try {
    const username = route.params.username; 
    
    const res = await axios.get(`http://localhost:8000/api/users/${username}/awards`);
    awards.value = res.data;
  } catch (err) {
    console.error("Chyba při načítání ocenění cizího profilu:", err);
  }
});
</script>