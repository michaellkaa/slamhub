<template>
  <div class="w-screen h-screen flex items-center justify-center bg-[#0f0f12] text-white p-4">
    <div class="bg-[#141418] rounded-xl shadow-xl p-8 w-full max-w-lg">
      <h1 class="text-2xl font-bold mb-6 text-center">
        Přidat Ocenění
      </h1>

      <form @submit.prevent="submitAward" class="flex flex-col gap-4">

        <label class="text-sm">
          Název ocenění <span class="text-pink-400">*</span>
        </label>
        <input
          v-model="award.title"
          required
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          placeholder="Např. Best Performer"
        />

        <label class="text-sm">
          Popis
        </label>
        <textarea
          v-model="award.description"
          rows="4"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          placeholder="Krátký popis ocenění"
        ></textarea>

        <label class="text-sm">
          Ikona / logo ocenění
        </label>
        <input
          type="file"
          @change="uploadIcon"
          class="p-3 rounded bg-[#1d1d21]"
        />

        <button
          type="submit"
          class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md mt-4"
        >
          Vytvořit ocenění
        </button>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const award = ref({
  title: '',
  description: '',
  icon: null
})


const uploadIcon = (e) => {
  const file = e.target.files[0]
  if (file) award.value.icon = file
}

const submitAward = async () => {
  const formData = new FormData()

  formData.append('title', award.value.title)
  formData.append('description', award.value.description ?? '')

  if (award.value.icon) {
    formData.append('icon', award.value.icon)
  }

  try {
    await axios.get('/sanctum/csrf-cookie')

    await axios.post('/api/awards', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    alert('Ocenění vytvořeno 🎉')

    award.value = {
      title: '',
      description: '',
      icon: null
    }

  } catch (err) {
    console.error(err.response?.data)
    alert(JSON.stringify(err.response?.data))
  }
}

</script>
