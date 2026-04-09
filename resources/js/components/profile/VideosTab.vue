<template>
  <div class="grid grid-cols-3 w-full lg:w-[50%] gap-4">
    <div
      v-for="video in videos"
      :key="video.id"
      class="aspect-[9/16] bg-black rounded-lg overflow-hidden relative"
    >
      <video
        :src="video.video_url"
        controls
        class="w-full h-full object-cover"
      ></video>
    </div>

    <div v-if="loading" v-for="n in 6" :key="'skeleton-' + n" class="aspect-[9/16] bg-[#1d1d21] rounded-lg animate-pulse"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: {
    type: String,
    required: true
  }
})

const videos = ref([])
const loading = ref(true)

const fetchVideos = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/users/${props.username}/videos`)
    videos.value = res.data
  } catch (err) {
    console.error('Chyba při načítání videí:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchVideos)
watch(() => props.username, fetchVideos)
</script>