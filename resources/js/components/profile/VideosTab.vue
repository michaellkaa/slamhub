<template>
  <div class="w-full">
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="n in 6"
        :key="'skeleton-' + n"
        class="rounded-2xl overflow-hidden bg-[#1d1d21] animate-pulse"
      >
        <div class="aspect-[9/16] bg-[#232328]"></div>
        <div class="p-2 space-y-2">
          <div class="h-3 w-3/4 rounded bg-white/10"></div>
          <div class="h-7 w-full rounded bg-white/5"></div>
        </div>
      </div>
    </div>

    <div v-else-if="!videos.length" class="text-center text-white/60 py-10">
      Zatim tu nejsou zadna videa.
    </div>

    <template v-else>
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-4 w-full max-w-4xl">
        <div
          v-for="(video, index) in videos"
          :key="video.id"
          class="rounded-2xl overflow-hidden bg-black relative text-left border border-white/10"
        >
          <button
            type="button"
            class="group block w-full aspect-[9/16] relative"
            @click="openVideo(video, index)"
          >
            <video
              :src="video.video_url"
              class="w-full h-full object-cover transition group-hover:scale-[1.02]"
              muted
              loop
              autoplay
              playsinline
            ></video>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-2 lg:p-3">
              <p class="text-white text-xs lg:text-sm font-semibold truncate">{{ video.title || 'Bez nazvu' }}</p>
            </div>
          </button>
          <div class="bg-[#141418] p-2 space-y-2">
            <div class="flex gap-2 text-xs">
              <button
                type="button"
                class="rounded-md px-2 py-1 transition inline-flex items-center gap-1.5"
                :class="video.liked_by_me ? 'bg-red-500/20 text-red-500' : 'bg-white/5 text-white/70 hover:bg-white/10'"
                @click="toggleLike(video)"
                aria-label="Like"
              >
                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="currentColor" aria-hidden="true">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5A5.5 5.5 0 0 1 7.5 3c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 3 5.5 5.5 0 0 1 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"/>
                </svg>
                <span>{{ video.likes_count || 0 }}</span>
              </button>
              <button
                type="button"
                class="rounded-md px-2 py-1 bg-white/5 text-white/70 hover:bg-white/10 transition inline-flex items-center gap-1.5"
                @click="openCommentsModal(video)"
                aria-label="Comments"
              >
                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                </svg>
                <span>{{ video.comments_count || 0 }}</span>
              </button>
            </div>

            <div v-if="video.showComments" class="space-y-2"></div>
          </div>
        </div>
      </div>
    </template>
  </div>

  <div
    v-if="selectedVideo"
    class="hidden lg:flex fixed inset-0 z-50 items-center justify-center bg-black/95 p-6"
    @click.self="closeVideo"
  >
    <button
      type="button"
      class="absolute top-4 right-4 rounded-lg bg-white/10 px-3 py-1.5 text-white hover:bg-white/20 z-10"
      @click="closeVideo"
    >
      Zavrit
    </button>
    <div class="w-full h-full flex items-center justify-center">
      <div class="w-full max-w-lg rounded-2xl overflow-hidden border border-white/15 bg-black relative">
        <video
          :src="selectedVideo.video_url"
          class="w-full max-h-[92vh] aspect-[9/16] object-contain"
          controls
          autoplay
        ></video>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex flex-col items-center gap-4 z-10">
          <button
            type="button"
            class="rounded-full"
            @click="openProfile(selectedVideo.user?.username)"
            aria-label="Open author profile"
          >
            <img
            :src="selectedVideo.user?.profile_pic_url || '/images/default-avatar.png'"
            class="w-10 h-10 rounded-full border border-white/40 object-cover shadow"
            :alt="selectedVideo.user?.username || props.username"
            />
          </button>
          <button
            type="button"
            class="inline-flex flex-col items-center transition"
            :class="selectedVideo.liked_by_me ? 'text-red-500' : 'text-white'"
            @click="toggleLike(selectedVideo)"
            aria-label="Like"
          >
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="currentColor" aria-hidden="true">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5A5.5 5.5 0 0 1 7.5 3c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 3 5.5 5.5 0 0 1 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"/>
            </svg>
            <span class="text-xs">{{ selectedVideo.likes_count || 0 }}</span>
          </button>
          <button
            type="button"
            class="inline-flex flex-col items-center text-white"
            @click="openCommentsModal(selectedVideo)"
            aria-label="Comments"
          >
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
            </svg>
            <span class="text-xs">{{ selectedVideo.comments_count || 0 }}</span>
          </button>
          <button
            type="button"
            class="inline-flex flex-col items-center text-white"
            @click="shareVideo(selectedVideo)"
            aria-label="Share"
          >
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/>
              <path d="M16 6l-4-4-4 4"/>
              <path d="M12 2v14"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

  </div>

  <div v-if="selectedVideo" class="lg:hidden fixed inset-0 z-50 bg-black">
    <button
      type="button"
      class="absolute top-4 right-4 rounded-lg bg-white/15 px-3 py-1.5 text-white z-20"
      @click="closeVideo"
    >
      Zavrit
    </button>

    <div ref="mobileViewerRef" class="h-full overflow-y-auto snap-y snap-mandatory">
      <section
        v-for="(video, index) in videos"
        :key="'mobile-view-' + video.id"
        :ref="(el) => setMobileSlideRef(el, index)"
        class="h-screen w-full snap-start flex items-center justify-center"
        style="scroll-snap-stop: always;"
      >
        <div class="relative w-full h-full">
          <video
            :src="video.video_url"
            class="w-full h-full object-contain"
            controls
            playsinline
            :autoplay="index === selectedIndex"
            preload="metadata"
          ></video>
          <div class="absolute right-3 top-1/2 -translate-y-1/2 flex flex-col items-center gap-4 z-10">
            <button
              type="button"
              class="rounded-full"
              @click="openProfile(video.user?.username)"
              aria-label="Open author profile"
            >
              <img
              :src="video.user?.profile_pic_url || '/images/default-avatar.png'"
              class="w-9 h-9 rounded-full border border-white/40 object-cover shadow"
              :alt="video.user?.username || props.username"
              />
            </button>
            <button
              type="button"
              class="inline-flex flex-col items-center transition"
              :class="video.liked_by_me ? 'text-red-500' : 'text-white'"
              @click="toggleLike(video)"
              aria-label="Like"
            >
              <svg viewBox="0 0 24 24" class="w-6 h-6" fill="currentColor" aria-hidden="true">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5A5.5 5.5 0 0 1 7.5 3c1.74 0 3.41.81 4.5 2.09A6 6 0 0 1 16.5 3 5.5 5.5 0 0 1 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"/>
              </svg>
              <span class="text-xs">{{ video.likes_count || 0 }}</span>
            </button>
            <button
              type="button"
              class="inline-flex flex-col items-center text-white"
              @click="openCommentsModal(video)"
              aria-label="Comments"
            >
              <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
              </svg>
              <span class="text-xs">{{ video.comments_count || 0 }}</span>
            </button>
            <button
              type="button"
              class="inline-flex flex-col items-center text-white"
              @click="shareVideo(video)"
              aria-label="Share"
            >
              <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/>
                <path d="M16 6l-4-4-4 4"/>
                <path d="M12 2v14"/>
              </svg>
            </button>
          </div>
        </div>
      </section>
    </div>
  </div>

  <div
    v-if="activeCommentVideo"
    class="fixed inset-0 z-[80] flex items-center justify-center bg-black/70 p-4"
    @click.self="closeCommentsModal"
  >
    <div class="w-full max-w-lg rounded-2xl border border-white/10 bg-[#121216]">
      <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
        <div class="text-sm font-semibold text-white">Comments</div>
        <button class="rounded-lg px-2 py-1 text-white/70 hover:bg-white/10" @click="closeCommentsModal">Close</button>
      </div>
      <div class="max-h-[55vh] overflow-y-auto px-4 py-3 space-y-3">
        <div v-if="activeCommentVideo.commentsLoading" class="text-xs text-white/50">Loading comments...</div>
        <div v-else-if="!activeCommentVideo.comments?.length" class="text-xs text-white/50">No comments yet.</div>
        <div v-else class="space-y-2">
          <div v-for="comment in activeCommentVideo.comments" :key="comment.id" class="rounded-xl bg-black/30 px-3 py-2.5">
            <div class="text-xs text-white/50">@{{ comment.user?.username || 'user' }}</div>
            <div class="text-sm text-white/85">{{ comment.body }}</div>
            <button
              type="button"
              class="mt-1 text-[11px] text-white/60 hover:text-white"
              @click="startReply(comment)"
            >
              Reply
            </button>
            <div v-if="comment.replies?.length" class="mt-2 ml-4 space-y-2 border-l border-white/10 pl-3">
              <div v-for="reply in comment.replies" :key="reply.id" class="rounded-lg bg-black/30 px-3 py-2">
                <div class="text-[11px] text-white/50">@{{ reply.user?.username || 'user' }}</div>
                <div class="text-sm text-white/85">{{ reply.body }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <form class="border-t border-white/10 p-3 flex gap-2" @submit.prevent="submitComment(activeCommentVideo)">
        <input
          v-model="activeCommentVideo.commentDraft"
          type="text"
          class="flex-1 rounded-lg bg-black/30 border border-white/10 px-3 py-2 text-sm text-white outline-none focus:border-pink-400"
          :placeholder="replyTargetCommentId ? 'Write a reply...' : 'Write a comment...'"
        />
        <button type="submit" class="rounded-lg bg-pink-500 px-3 py-2 text-sm font-semibold text-white hover:bg-pink-600">
          Send
        </button>
      </form>
      <div v-if="replyTargetCommentId" class="px-3 pb-3">
        <button class="text-xs text-white/60 hover:text-white" @click="replyTargetCommentId = null">Cancel reply</button>
      </div>
    </div>
  </div>

  <div
    v-if="shareNotice"
    class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[85] rounded-lg bg-black/80 px-3 py-2 text-xs text-white"
  >
    {{ shareNotice }}
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const props = defineProps({
  username: {
    type: String,
    required: true
  }
})

const videos = ref([])
const loading = ref(true)
const selectedVideo = ref(null)
const selectedIndex = ref(0)
const mobileViewerRef = ref(null)
const mobileSlideRefs = ref([])
const activeCommentVideo = ref(null)
const replyTargetCommentId = ref(null)
const shareNotice = ref('')
const router = useRouter()

const fetchVideos = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/users/${props.username}/videos`)
    videos.value = (Array.isArray(res.data) ? res.data : []).map((video) => ({
      ...video,
      likes_count: video.likes_count || 0,
      comments_count: video.comments_count || 0,
      liked_by_me: !!video.liked_by_me,
      showComments: false,
      commentsLoading: false,
      comments: [],
      commentDraft: ''
    }))
  } catch (err) {
    console.error('Chyba pri nacitani videi:', err)
    videos.value = []
  } finally {
    loading.value = false
  }
}

const setMobileSlideRef = (el, index) => {
  mobileSlideRefs.value[index] = el
}

const openVideo = async (video, index = 0) => {
  selectedVideo.value = video
  selectedIndex.value = index

  await nextTick()

  const target = mobileSlideRefs.value[index]
  if (target && typeof target.scrollIntoView === 'function') {
    target.scrollIntoView({ block: 'start' })
  } else if (mobileViewerRef.value) {
    mobileViewerRef.value.scrollTop = (window.innerHeight || 0) * index
  }
}

const closeVideo = () => {
  selectedVideo.value = null
  selectedIndex.value = 0
}

onMounted(fetchVideos)
watch(() => props.username, fetchVideos)

const hasToken = () => Boolean(localStorage.getItem('token'))

const toggleLike = async (video) => {
  if (!hasToken()) return
  try {
    const { data } = await axios.post(`/api/videos/${video.id}/like`)
    video.liked_by_me = !!data?.liked
    video.likes_count = data?.likes_count ?? video.likes_count
  } catch (err) {
    console.error(err)
  }
}

const fetchComments = async (video) => {
  video.commentsLoading = true
  try {
    const { data } = await axios.get(`/api/videos/${video.id}/comments`)
    video.comments = Array.isArray(data) ? data : []
  } catch (err) {
    console.error(err)
    video.comments = []
  } finally {
    video.commentsLoading = false
  }
}

const toggleComments = async (video) => {
  video.showComments = !video.showComments
  if (video.showComments) {
    await fetchComments(video)
  }
}

const submitComment = async (video) => {
  if (!hasToken() || !video.commentDraft.trim()) return
  try {
    await axios.post(`/api/videos/${video.id}/comments`, {
      body: video.commentDraft.trim(),
      parent_id: replyTargetCommentId.value || null
    })
    video.comments_count += 1
    video.commentDraft = ''
    replyTargetCommentId.value = null
    await fetchComments(video)
  } catch (err) {
    console.error(err)
  }
}

const shareVideo = async (video) => {
  if (!video?.slug) return
  const link = `${window.location.origin}/videos/${video.slug}`
  try {
    if (navigator.share) {
      await navigator.share({ url: link })
      shareNotice.value = 'Share opened'
      setTimeout(() => { shareNotice.value = '' }, 1200)
      return
    }
  } catch {}
  try {
    await navigator.clipboard.writeText(link)
    shareNotice.value = 'Link copied'
    setTimeout(() => { shareNotice.value = '' }, 1200)
  } catch (err) {
    console.error(err)
  }
}

const openProfile = (username) => {
  if (!username) return
  router.push(`/profile/${username}`)
}

const openCommentsModal = async (video) => {
  activeCommentVideo.value = video
  if (!video.comments?.length) {
    await fetchComments(video)
  }
}

const closeCommentsModal = () => {
  activeCommentVideo.value = null
  replyTargetCommentId.value = null
}

const startReply = (comment) => {
  replyTargetCommentId.value = comment.id
}
</script>