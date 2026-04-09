<template>
  <div class="bg-[#0f0f12] w-screen min-h-screen flex flex-col lg:flex-row overflow-hidden">
    <div class="lg:h-full lg:w-28 w-full  fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex gap-6 lg:p-6 overflow-hidden no-scrollbar">
      <div
        class="flex-1 rounded-xl lg:p-4 overflow-y-auto lg:max-h-[90vh] max-h-full no-scrollbar"
        style="scroll-snap-type: y mandatory;"
      >
        <div
          v-for="video in videos"
          :key="video.id"
          class="mb-6 flex flex-col items-center justify-center gap-3"
          style="scroll-snap-align: start;"
        >
          <video
            :src="video.video_url"
            class="w-full max-w-md rounded-lg aspect-[9/16] object-cover"
            autoplay
            muted
            loop
            playsinline
          ></video>

          <div class="w-full max-w-md text-white/80 mt-2 p-3 lg:p-0">
            <p class="font-semibold">{{ video.title || 'Bez názvu' }}</p>
            <p class="text-sm">{{ video.description }}</p>
          </div>
        </div>

        <div v-if="loading" v-for="n in 3" :key="'skeleton-' + n" class="mb-6 flex flex-col items-center gap-3 animate-pulse">
          <div class="w-full max-w-md h-[400px] bg-[#1d1d21] rounded-lg"></div>
          <div class="w-full max-w-md h-4 bg-[#1d1d21] rounded"></div>
          <div class="w-full max-w-md h-3 bg-[#1d1d21] rounded w-3/4"></div>
        </div>
      </div>

      <div class="hidden w-80 lg:flex flex-col gap-6 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-center">
          <TopSearch />
        </div>

        <div class="rounded-xl shadow-lg p-4">
          <div class="h-4 mb-3 bg-[#1d1d21] rounded w-3/4"></div>
          <div v-for="n in 4" :key="'people-' + n" class="flex items-center mb-3">
            <div class="w-12 h-12 rounded-full bg-[#1d1d21] mr-3"></div>
            <div class="flex-1">
              <div class="h-4 bg-[#1d1d21] rounded mb-1 w-3/4"></div>
              <div class="h-3 bg-[#1d1d21] rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div class="rounded-xl shadow-lg p-4">
          <div class="h-4 mb-3 bg-[#1d1d21] rounded w-3/4"></div>
          <div v-for="n in 3" :key="'event-' + n" class="mb-3">
            <div class="h-4 bg-[#1d1d21] rounded mb-1 w-3/4"></div>
            <div class="h-3 bg-[#1d1d21] rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import SideNav from '../components/SideNav.vue'
import TopSearch from '../components/TopSearch.vue'

const videos = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await axios.get('/api/videos')
    videos.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.flex-1::-webkit-scrollbar {
  display: none;
}
</style>
