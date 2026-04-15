<template>
  <div class="bg-[#0f0f12] w-full min-h-screen flex flex-col lg:flex-row overflow-hidden">
    <div class="lg:h-full lg:w-28 w-full  fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex gap-6 lg:p-6 overflow-hidden no-scrollbar">
      <div
        class="flex-1 rounded-xl lg:p-4 overflow-y-auto lg:max-h-[90vh] max-h-full no-scrollbar snap-y snap-mandatory touch-pan-y overscroll-y-contain pb-28 lg:pb-0"
        style="scroll-snap-type: y mandatory;"
      >
        <div
          v-for="video in videos"
          :key="video.id"
          class="mb-4 lg:mb-6 flex flex-col items-center justify-center gap-3 snap-start min-h-[calc(100svh-8rem)] lg:min-h-0"
          style="scroll-snap-align: start; scroll-snap-stop: always;"
        >
          <button
            type="button"
            class="w-full max-w-md text-left rounded-2xl overflow-hidden bg-[#121216] group"
            @click="openVideo(video)"
          >
            <div class="relative aspect-[9/16] bg-black">
              <video
                :src="video.video_url"
                class="w-full h-full object-cover transition group-hover:scale-[1.01]"
                autoplay
                muted
                loop
                playsinline
              ></video>
              <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>
            </div>

            <div class="w-full text-white/80 mt-1 p-3">
              <p class="font-semibold truncate">{{ video.title || 'Bez nazvu' }}</p>
              <p class="text-sm text-white/65 line-clamp-2">{{ video.description || 'Bez popisu' }}</p>
            </div>
          </button>
        </div>

        <div v-if="!loading && !videos.length" class="text-center text-white/60 py-10">
          Zatim tu nejsou zadna videa.
        </div>

        <div v-if="loading" v-for="n in 3" :key="'skeleton-' + n" class="mb-6 flex flex-col items-center gap-3 animate-pulse">
          <div class="w-full max-w-md aspect-[9/16] bg-[#1d1d21] rounded-2xl"></div>
          <div class="w-full max-w-md h-4 bg-[#1d1d21] rounded"></div>
          <div class="w-full max-w-md h-3 bg-[#1d1d21] rounded "></div>
        </div>
      </div>

      <div
        v-if="selectedVideo"
        class="hidden lg:flex fixed inset-0 z-50 items-center justify-center bg-black/80 p-6"
        @click.self="closeVideo"
      >
        <div class="relative w-full max-w-5xl flex justify-center">
          <button
            type="button"
            class="absolute -top-2 right-0 rounded-lg bg-white/10 px-3 py-1.5 text-white hover:bg-white/20"
            @click="closeVideo"
          >
            Zavrit
          </button>
          <div class="w-full max-w-md rounded-2xl overflow-hidden border border-white/15 bg-black">
            <video
              :src="selectedVideo.video_url"
              class="w-full max-h-[86vh] aspect-[9/16] object-contain"
              controls
              autoplay
            ></video>
          </div>
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
const selectedVideo = ref(null)

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

const openVideo = (video) => {
  selectedVideo.value = video
}

const closeVideo = () => {
  selectedVideo.value = null
}

const activeNav = ref('home')
  
  const handleNavigate = (nav) => {
    activeNav.value = nav
  }
</script>

<style scoped>
.flex-1::-webkit-scrollbar {
  display: none;
}
</style>
