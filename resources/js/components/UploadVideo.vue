<template>
  <div class="max-w-md mx-auto p-4 space-y-4">

    <div v-if="uploading" class="space-y-2">
      <div class="h-64 bg-[#1d1d21] rounded animate-pulse"></div>
      <div class="h-6 bg-[#1d1d21] rounded w-3/4 animate-pulse"></div>
      <div class="h-4 bg-[#1d1d21] rounded w-1/2 animate-pulse"></div>
    </div>

    <div v-else class="flex flex-col gap-4">
      <div 
        class="border-2 border-dashed border-white/20 rounded-xl h-64 flex items-center justify-center cursor-pointer hover:border-pink-500 transition"
        @click="triggerFile"
      >
        <span v-if="!file">Klikni nebo přetáhni video sem</span>
        <video 
          v-if="filePreview" 
          :src="filePreview" 
          class="h-full w-full object-cover rounded-xl" 
          autoplay 
          muted 
          loop
        ></video>
      </div>

      <input type="file" ref="fileInput" class="hidden" @change="handleFile" accept="video/*" />

      <input v-model="title" placeholder="Název videa" class="p-2 rounded bg-[#1d1d21] text-white w-full" />
      <textarea v-model="description" placeholder="Popis videa" class="p-2 rounded bg-[#1d1d21] text-white w-full"></textarea>

      <select v-model="status" class="p-2 rounded bg-[#1d1d21] text-white w-full">
        <option value="public">Public</option>
        <option value="unlisted">Unlisted</option>
        <option value="private">Private</option>
      </select>

      <button 
        @click="upload"
        class="bg-pink-500 hover:bg-pink-600 transition text-white px-4 py-2 rounded font-semibold"
        :disabled="!file"
      >
        Nahrát video
      </button>

      <div v-if="videoSlug" class="text-white/70 mt-2">
        Sdílecí link: 
        <a :href="`${window.location.origin}/videos/${videoSlug}`" target="_blank" class="underline text-pink-400">
          {{ window.location.origin }}/videos/{{ videoSlug }}
        </a>
      </div>

      <div v-if="error" class="text-red-500">{{ error }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const file = ref(null)
const filePreview = ref(null)
const title = ref('')
const description = ref('')
const status = ref('public')
const uploading = ref(false)
const error = ref(null)
const videoSlug = ref(null)
const fileInput = ref(null)

const triggerFile = () => fileInput.value.click()

const handleFile = (e) => {
  file.value = e.target.files[0]
  if (file.value) {
    filePreview.value = URL.createObjectURL(file.value)
  }
}

const upload = async () => {
  if (!file.value) return
  uploading.value = true
  error.value = null

  const formData = new FormData()
  formData.append('video', file.value)
  formData.append('title', title.value)
  formData.append('description', description.value)
  formData.append('status', status.value)

  try {
    const token = localStorage.getItem('token')
    axios.defaults.headers.common.Authorization = `Bearer ${token}`

    const res = await axios.post('/api/videos', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    videoSlug.value = res.data.slug

    file.value = null
    filePreview.value = null
    title.value = ''
    description.value = ''
    status.value = 'public'
  } catch (e) {
    error.value = e.response?.data?.message || 'Chyba při nahrávání'
  } finally {
    uploading.value = false
  }
}
</script>

