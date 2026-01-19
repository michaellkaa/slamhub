<template>
  <div class="w-screen h-screen flex items-center justify-center bg-[#0f0f12] text-white p-4">
    <div class="bg-[#141418] rounded-xl shadow-xl p-8 w-full max-w-lg">
      <h1 class="text-2xl font-bold mb-6 text-center">Přidat Post</h1>

      <form @submit.prevent="submitPost" class="flex flex-col gap-4">
        <label class="text-sm">Obsah příspěvku <span class="text-pink-400">*</span></label>
        <textarea
          v-model="post.body"
          required
          rows="6"
          class="p-3 rounded bg-[#1d1d21] focus:ring-2 focus:ring-pink-500 outline-none"
          placeholder="Napiš něco..."
        ></textarea>

        <button
          type="submit"
          class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md mt-4"
        >
          Vytvořit příspěvek
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const post = ref({
  body: ''
})

const submitPost = async () => {
  try {
    await axios.post('/api/posts', {
      ...post.value,
      status: 1
    })
    post.value.body = ''
  } catch (err) {
    console.error(err.response?.data)
    alert('Chyba při vytváření příspěvku: ' + JSON.stringify(err.response?.data))
  }
}
</script>
