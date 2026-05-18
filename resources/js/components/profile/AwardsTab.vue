<template>
  <div class="mt-8 w-full lg:w-[70%] space-y-4">
    <div v-if="awards.length > 0">
      <div v-for="award in awards" :key="award.id" class="flex items-center gap-4 p-4 bg-[#1d1d21] rounded-xl mb-4">
        <img :src="award.icon_url" class="w-10 h-10 object-contain" />
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
      žádná ocenění nenalezena...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

axios.defaults.baseURL = window.location.origin
axios.defaults.withCredentials = true

const awards = ref([])
const route = useRoute()

const fetchAwards = async () => {
  const username = route.params.username;
  if (!username) return;

  try {
    const res = await axios.get(`/api/users/${username}/awards`);
    awards.value = res.data;
  } catch (err) {
    console.error("Chyba při načítání ocenění:", err);
    awards.value = [];
  }
}

watch(
  () => route.params.username,
  () => fetchAwards()
)

onMounted(fetchAwards);
</script>