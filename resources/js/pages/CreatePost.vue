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
<div class="text-xs flex justify-between"
     :class="isTooLong ? 'text-red-500' : 'text-white/50'">

  <span>
    {{ post.body.length }} / {{ MAX_LENGTH }}
  </span>

  <span v-if="isTooLong">
    Příliš dlouhý text
  </span>
</div>
        <button
  type="submit"
  :disabled="isTooLong || !post.body"
  class="bg-pink-500 hover:bg-pink-600 transition-colors text-white font-bold py-3 rounded shadow-md mt-4 disabled:opacity-50"
>
          Vytvořit příspěvek
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const MAX_LENGTH = 500

const post = ref({
  body: ''
})

const remainingChars = computed(() => {
  return MAX_LENGTH - post.value.body.length
})

const isTooLong = computed(() => {
  return post.value.body.length > MAX_LENGTH
})
//ukazovat pocet znaku kdyz pisu post
//nepresmerovava me to


const submitPost = async () => {
  if (isTooLong.value) return

  try {
    await axios.post('/api/posts', {
      ...post.value,
      status: 1
    })

    post.value.body = ''

    window.location.href = '/profile'
  } catch (err) {
    console.error(err.response?.data)
    alert('Chyba při vytváření příspěvku: ' + JSON.stringify(err.response?.data))
  }
}
</script>
