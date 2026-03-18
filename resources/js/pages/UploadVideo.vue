<template>
  <div class="w-screen h-screen flex items-center justify-center bg-[#0f0f12] text-white p-4">
    
    <div class="bg-[#141418] rounded-xl shadow-xl p-8 w-full max-w-lg">

      <h1 class="text-2xl font-bold mb-6 text-center">
        Nahrát video
      </h1>

      <form @submit.prevent="upload" class="flex flex-col gap-4">

        <label class="text-sm">
          Video <span class="text-pink-400">*</span>
        </label>

        <!-- VIDEO PREVIEW -->
        <div
          class="border-2 border-dashed border-white/20 rounded-xl w-full aspect-video relative flex items-center justify-center cursor-pointer hover:border-pink-500 transition bg-black overflow-hidden"
          @click="triggerFile"
        >

          <span v-if="!filePreview" class="text-white/60 text-sm">
            Klikni nebo přetáhni video
          </span>

          <video
            v-if="filePreview"
            :src="filePreview"
            controls
            class="absolute inset-0 w-full h-full object-contain"
          ></video>

        </div>

        <div v-if="file" class="text-xs text-white/60 truncate">
          {{ file.name }}
        </div>

        <input
          type="file"
          ref="fileInput"
          class="hidden"
          @change="handleFile"
          accept="video/*"
        />

        <label class="text-sm">
          Název videa <span class="text-pink-400">*</span>
        </label>

        <input
          v-model="title"
          required
          placeholder="Např. Slam poetry vystoupení"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        />

        <label class="text-sm">
          Popis videa
        </label>

        <textarea
          v-model="description"
          rows="4"
          placeholder="Krátký popis videa"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        ></textarea>

        <label class="text-sm">
          Viditelnost
        </label>

        <select
          v-model="status"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        >
          <option value="public">Public</option>
          <option value="unlisted">Unlisted</option>
          <option value="private">Private</option>
        </select>

        <button
          type="submit"
          :disabled="!file || uploading"
          class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md mt-4 disabled:opacity-50"
        >
          {{ uploading ? 'Nahrávám...' : 'Nahrát video' }}
        </button>

        <div v-if="videoSlug" class="text-white/70 text-sm mt-2 text-center break-all">
          Sdílecí link:
          <a
            :href="baseUrl + '/videos/' + videoSlug"
            target="_blank"
            class="underline text-pink-400"
          >
            {{ baseUrl + '/videos/' + videoSlug }}
          </a>
        </div>

        <div v-if="error" class="text-red-500 text-sm text-center">
          {{ error }}
        </div>

      </form>

    </div>

  </div>
</template>

<script setup>
import { ref } from "vue"
import axios from "axios"

const file = ref(null)
const filePreview = ref(null)
const title = ref("")
const description = ref("")
const status = ref("public")
const uploading = ref(false)
const error = ref(null)
const videoSlug = ref(null)
const fileInput = ref(null)
const baseUrl = window.location.origin

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
  formData.append("video", file.value)
  formData.append("title", title.value)
  formData.append("description", description.value)
  formData.append("status", status.value)

  try {
    const token = localStorage.getItem("token")

    const res = await axios.post("/api/videos", formData, {
      headers: {
        Authorization: `Bearer ${token}`
      },
      maxContentLength: Infinity,
      maxBodyLength: Infinity
    })

    videoSlug.value = res.data.slug

  } catch (e) {
    console.error(e)
    error.value = e.response?.data?.message || "Chyba při nahrávání"
  } finally {
    uploading.value = false
  }
}
</script>