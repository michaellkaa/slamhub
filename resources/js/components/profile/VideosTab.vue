<template>
  <div class="w-full">
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="n in 6"
        :key="'skeleton-' + n"
        class="aspect-[9/16] bg-[#1d1d21] rounded-2xl animate-pulse"
      ></div>
    </div>

    <div v-else-if="!videos.length" class="text-center text-white/60 py-10">
      Zatim tu nejsou zadna videa.
    </div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4 w-full max-w-4xl">
        <button
          v-for="(video, index) in videos"
          :key="video.id"
          type="button"
          class="group aspect-[9/16] rounded-2xl overflow-hidden bg-black relative text-left border border-white/10"
          @click="openVideo(video, index)"
        >
          <video
            :src="video.video_url"
            class="w-full h-full object-cover transition group-hover:scale-[1.02]"
            muted
            loop
            autoplay
            playsinline
          ></video>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
          <div class="absolute bottom-0 left-0 right-0 p-2 lg:p-3">
            <p class="text-white text-xs lg:text-sm font-semibold truncate">{{ video.title || 'Bez nazvu' }}</p>
          </div>
        </button>
      </div>
    </template>
  </div>

  <div
    v-if="selectedVideo"
    class="hidden lg:flex fixed inset-0 z-50 items-center justify-center bg-black/95 p-6"
    @click.self="closeVideo"
  >
    <button
      type="button"
      class="absolute top-4 right-4 rounded-lg bg-white/10 px-3 py-1.5 text-white hover:bg-white/20 z-10"
      @click="closeVideo"
    >
      Zavrit
    </button>
    <div class="w-full h-full flex items-center justify-center">
      <div class="w-full max-w-lg rounded-2xl overflow-hidden border border-white/15 bg-black">
        <video
          :src="selectedVideo.video_url"
          class="w-full max-h-[92vh] aspect-[9/16] object-contain"
          controls
          autoplay
        ></video>
      </div>
    </div>
  </div>

  <div v-if="selectedVideo" class="lg:hidden fixed inset-0 z-50 bg-black">
    <button
      type="button"
      class="absolute top-4 right-4 rounded-lg bg-white/15 px-3 py-1.5 text-white z-20"
      @click="closeVideo"
    >
      Zavrit
    </button>

    <div ref="mobileViewerRef" class="h-full overflow-y-auto snap-y snap-mandatory">
      <section
        v-for="(video, index) in videos"
        :key="'mobile-view-' + video.id"
        :ref="(el) => setMobileSlideRef(el, index)"
        class="h-screen w-full snap-start flex items-center justify-center"
        style="scroll-snap-stop: always;"
      >
        <video
          :src="video.video_url"
          class="w-full h-full object-contain"
          controls
          playsinline
          :autoplay="index === selectedIndex"
          preload="metadata"
        ></video>
      </section>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: {
    type: String,
    required: true
  }
})

const videos = ref([])
const loading = ref(true)
const selectedVideo = ref(null)
const selectedIndex = ref(0)
const mobileViewerRef = ref(null)
const mobileSlideRefs = ref([])

const fetchVideos = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/users/${props.username}/videos`)
    videos.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Chyba pri nacitani videi:', err)
    videos.value = []
  } finally {
    loading.value = false
  }
}

const setMobileSlideRef = (el, index) => {
  mobileSlideRefs.value[index] = el
}

const openVideo = async (video, index = 0) => {
  selectedVideo.value = video
  selectedIndex.value = index

  await nextTick()

  const target = mobileSlideRefs.value[index]
  if (target && typeof target.scrollIntoView === 'function') {
    target.scrollIntoView({ block: 'start' })
  } else if (mobileViewerRef.value) {
    mobileViewerRef.value.scrollTop = (window.innerHeight || 0) * index
  }
}

const closeVideo = () => {
  selectedVideo.value = null
  selectedIndex.value = 0
}

onMounted(fetchVideos)
watch(() => props.username, fetchVideos)
</script>