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

                <div class="border-2 border-dashed border-white/20 rounded-xl w-full aspect-video relative flex items-center justify-center cursor-pointer hover:border-pink-500 transition bg-black overflow-hidden"
                    @click="triggerFile" @dragover.prevent @drop.prevent="handleDrop">

                    <span v-if="!filePreview" class="text-white/60 text-sm">
                        Klikni nebo přetáhni video
                    </span>

                    <video v-if="filePreview" :src="filePreview" controls
                        class="absolute inset-0 w-full h-full object-contain"></video>

                </div>

                <div v-if="file" class="text-xs text-white/60 truncate">
                    {{ file.name }}
                </div>

                <input type="file" ref="fileInput" class="hidden" @change="handleFile" accept="video/*" />

                <label class="text-sm">
                    Název videa <span class="text-pink-400">*</span>
                </label>

                <input v-model="title" required placeholder="Např. Slam poetry vystoupení"
                    class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none" />

<label class="text-sm">
  Popis videa (max {{ DESC_LIMIT }} znaků)
</label>

<textarea
  v-model="description"
  rows="4"
  :maxlength="DESC_LIMIT"
  placeholder="Krátký popis videa"
  class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
></textarea>

<div class="text-xs text-white/40 mt-1 flex justify-between">
  <span>{{ description.length }}/{{ DESC_LIMIT }}</span>

  <span v-if="description.length >= DESC_LIMIT" class="text-pink-400">
    Limit dosažen
  </span>
</div>



                <label class="text-sm text-white/70">
                    Viditelnost
                </label>

                <!--<select
          v-model="status"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
        >
          <option value="public">Toto video bude nastaveno jako veřejné</option>
          <option value="unlisted">Unlisted</option>
          <option value="private">Private</option>
        </select>-->
                <div class="flex flex-col">
                    <span class="text-sm text-white">
                        Veřejné
                    </span>
                    <span class="text-xs text-white/40">
                        Video bude viditelné pro všechny uživatele
                    </span>
                </div>
                <div v-if="uploading" class="text-sm text-center">
                    {{ progress }} %
                </div>

                <button type="submit" :disabled="!file || uploading"
                    class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md mt-2 disabled:opacity-50">
                    {{ uploading ? 'Nahrávám...' : 'Nahrát video' }}
                </button>

                <!--<div v-if="videoSlug" class="text-white/70 text-sm mt-2 text-center break-all">
          Sdílecí link:
          <a
            :href="baseUrl + '/videos/' + videoSlug"
            target="_blank"
            class="underline text-pink-400"
          >
            {{ baseUrl + '/videos/' + videoSlug }}
          </a>
        </div>-->

                <div v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </div>

            </form>

        </div>

    </div>
</template>

<script setup>
import { useRouter } from "vue-router"
import axios from "axios"
import { ref, onUnmounted, computed } from "vue"
const file = ref(null)
const filePreview = ref(null)
const title = ref("")
const description = ref("")

const uploading = ref(false)
const progress = ref(0)
const error = ref(null)
const videoSlug = ref(null)
const fileInput = ref(null)

const baseUrl = window.location.origin

const router = useRouter()

const MAX_SIZE = 200 * 1024 * 1024 // 200MB

const DESC_LIMIT = 150
const expanded = ref(false)

const shortDescription = computed(() => {
    if (!description.value) return ''
    if (description.value.length <= DESC_LIMIT) return description.value
    return description.value.slice(0, DESC_LIMIT) + '...'
})

const triggerFile = () => {
    fileInput.value.value = null
    fileInput.value.click()
}

const setFile = (newFile) => {
    if (!newFile) return

    if (!newFile.type.startsWith("video/")) {
        error.value = "Soubor není video"
        return
    }

    if (newFile.size > MAX_SIZE) {
        error.value = "Video je příliš velké (max 200 MB)"
        return
    }

    error.value = null

    if (filePreview.value) {
        URL.revokeObjectURL(filePreview.value)
    }

    file.value = newFile
    filePreview.value = URL.createObjectURL(newFile)
}

const handleFile = (e) => {
    const newFile = e.target.files[0]
    setFile(newFile)
}

const handleDrop = (e) => {
    const droppedFile = e.dataTransfer.files[0]
    setFile(droppedFile)
}

const upload = async () => {
    if (!file.value) return

    uploading.value = true
    error.value = null
    progress.value = 0

    const formData = new FormData()
    formData.append("video", file.value)
    formData.append("title", title.value)
    formData.append("description", description.value)

    formData.append("status", "public")

    try {
        const token = localStorage.getItem("token")

        const res = await axios.post("/api/videos", formData, {
            headers: {
                Authorization: `Bearer ${token}`
            },
            onUploadProgress: (e) => {
                if (e.total) {
                    progress.value = Math.round((e.loaded * 100) / e.total)
                }
            }
        })

        videoSlug.value = res.data.slug

        file.value = null
        filePreview.value = null
        title.value = ""
        description.value = ""
        setTimeout(() => {
            router.push("/")
        }, 500)
    } catch (e) {
        console.error(e)
        error.value = e.response?.data?.message || "Chyba při nahrávání"
    } finally {
        uploading.value = false
    }
}

onUnmounted(() => {
    if (filePreview.value) {
        URL.revokeObjectURL(filePreview.value)
    }
})
</script>
