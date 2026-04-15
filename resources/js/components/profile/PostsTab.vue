<template>
  <div class="mt-8 w-full lg:w-[50%] space-y-6">
    <div v-if="loading" class="space-y-4">
      <div
        v-for="n in 3"
        :key="'post-skeleton-' + n"
        class="p-4 bg-[#1d1d21] rounded-xl animate-pulse space-y-3"
      >
        <div class="h-4 w-full bg-white/10 rounded"></div>
        <div class="h-4 w-5/6 bg-white/10 rounded"></div>
        <div class="h-3 w-24 bg-white/10 rounded"></div>
        <div class="h-8 w-full bg-white/5 rounded-lg"></div>
      </div>
    </div>

    <div
      v-else
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

      <div class="mt-3 flex items-center gap-3 text-sm">
        <button
          type="button"
          class="rounded-lg px-3 py-1.5 transition inline-flex items-center gap-1.5"
          :class="post.liked_by_me ? 'bg-red-500/20 text-red-500' : 'bg-white/5 text-white/70 hover:bg-white/10'"
          @click="toggleLike(post)"
          aria-label="Like"
        >
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5A5.5 5.5 0 0 1 7.5 3c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 3 5.5 5.5 0 0 1 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"/>
          </svg>
          <span>{{ post.likes_count || 0 }}</span>
        </button>
        <button
          type="button"
          class="rounded-lg px-3 py-1.5 bg-white/5 text-white/70 hover:bg-white/10 transition inline-flex items-center gap-1.5"
          @click="toggleComments(post)"
          aria-label="Comments"
        >
          <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
          </svg>
          <span>{{ post.comments_count || 0 }}</span>
        </button>
      </div>

      <div v-if="post.showComments" class="mt-3 space-y-3">
        <form class="flex gap-2" @submit.prevent="submitComment(post)">
          <input
            v-model="post.commentDraft"
            type="text"
            class="flex-1 rounded-lg bg-[#121216] border border-white/10 px-3 py-2 text-sm text-white outline-none focus:border-pink-400"
            placeholder="Write a comment..."
          />
          <button type="submit" class="rounded-lg bg-pink-500 px-3 py-2 text-sm font-semibold text-white hover:bg-pink-600">
            Send
          </button>
        </form>

        <div v-if="post.commentsLoading" class="text-xs text-white/50">Loading comments...</div>
        <div v-else-if="!post.comments?.length" class="text-xs text-white/50">No comments yet.</div>
        <div v-else class="space-y-2">
          <div v-for="comment in post.comments" :key="comment.id" class="rounded-lg bg-black/20 px-3 py-2">
            <div class="text-xs text-white/50">@{{ comment.user?.username || 'user' }}</div>
            <div class="text-sm text-white/85">{{ comment.body }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!loading && !posts.length" class="text-sm text-white/50">
      No posts yet.
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: String
})

const posts = ref([])
const loading = ref(true)

const loadPosts = async () => {
  if (!props.username) return
  loading.value = true
  try {
    const res = await axios.get(`/api/users/${props.username}/posts`)
    posts.value = (Array.isArray(res.data) ? res.data : []).map((post) => ({
      ...post,
      likes_count: post.likes_count || 0,
      comments_count: post.comments_count || 0,
      liked_by_me: !!post.liked_by_me,
      showComments: false,
      commentsLoading: false,
      comments: [],
      commentDraft: ''
    }))
  } catch (err) {
    console.error(err)
    posts.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadPosts)
watch(() => props.username, loadPosts)

const hasToken = () => Boolean(localStorage.getItem('token'))

const toggleLike = async (post) => {
  if (!hasToken()) return
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/like`)
    post.liked_by_me = !!data?.liked
    post.likes_count = data?.likes_count ?? post.likes_count
  } catch (err) {
    console.error(err)
  }
}

const fetchComments = async (post) => {
  post.commentsLoading = true
  try {
    const { data } = await axios.get(`/api/posts/${post.id}/comments`)
    post.comments = Array.isArray(data) ? data : []
  } catch (err) {
    console.error(err)
    post.comments = []
  } finally {
    post.commentsLoading = false
  }
}

const toggleComments = async (post) => {
  post.showComments = !post.showComments
  if (post.showComments) {
    await fetchComments(post)
  }
}

const submitComment = async (post) => {
  if (!hasToken() || !post.commentDraft.trim()) return
  try {
    const { data } = await axios.post(`/api/posts/${post.id}/comments`, {
      body: post.commentDraft.trim()
    })
    post.comments.unshift(data)
    post.comments_count += 1
    post.commentDraft = ''
  } catch (err) {
    console.error(err)
  }
}

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
