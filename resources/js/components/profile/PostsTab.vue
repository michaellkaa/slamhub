<template>
  <div class="mt-8 w-[50%] space-y-6">

    <div
      v-for="post in posts"
      :key="post.id"
      class="p-4 bg-[#1d1d21] rounded-xl"
    >
      <p class="text-white/90 leading-relaxed">
        {{ post.content }}
      </p>

      <div class="text-xs text-white/40 mt-2">
        {{ post.created_at }}
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const posts = ref([])

onMounted(async () => {
  const res = await axios.get('/api/profile/posts')
  posts.value = res.data
})
</script>
