<template>
  <div class="mt-8 w-[50%] space-y-6">

    <div
      v-for="post in posts"
      :key="post.id"
      class="p-4 bg-[#1d1d21] rounded-xl"
    >
      <p class="text-white/90 leading-relaxed">
        {{ post.body }}
      </p>

      <div class="text-xs text-white/40 mt-2">
        {{ timeAgo(post.created_at) }}
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

const timeAgo = (dateString) => {
  const date = new Date(dateString)
  const seconds = Math.floor((new Date() - date) / 1000)

  const intervals = {
    year: 31536000,
    month: 2592000,
    day: 86400,
    hour: 3600,
    minute: 60,
  }

  for (const [label, secondsInUnit] of Object.entries(intervals)) {
    const count = Math.floor(seconds / secondsInUnit)
    if (count >= 1) {
      return `${count} ${label}${count > 1 ? 's' : ''} ago`
    }
  }

  return 'just now'
}


</script>
