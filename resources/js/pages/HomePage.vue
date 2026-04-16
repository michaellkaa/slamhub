<template>
  <div class="bg-[#0f0f12] w-full min-h-screen flex flex-col lg:flex-row overflow-hidden">
    <div class="lg:h-full lg:w-28 w-full  fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex gap-6 lg:p-6 overflow-hidden no-scrollbar">
      <div
        class="flex-1 rounded-xl lg:p-4 overflow-y-auto lg:max-h-[90vh] max-h-full no-scrollbar touch-pan-y overscroll-y-contain snap-y snap-mandatory pb-36 lg:pb-0"
        style="scroll-snap-type: y mandatory;"
      >
        <div class="sticky top-0 z-20 bg-[#0f0f12] px-4 pt-4 pb-3 lg:hidden">
            <TopSearch />
        </div>

        <div
          v-for="video in videos"
          :key="video.id"
          class="mb-4 lg:mb-6 flex flex-col items-center justify-center gap-3 snap-start min-h-[calc(100svh-10rem)] lg:min-h-[calc(100vh-6rem)]"
          style="scroll-snap-align: start; scroll-snap-stop: always;"
        >
          <div
            class="w-full max-w-md text-left rounded-2xl overflow-hidden bg-[#121216] group"
          >
            <div class="relative aspect-[9/16] bg-black">
              <video
                :src="video.video_url"
                class="w-full h-full object-cover transition group-hover:scale-[1.01]"
                autoplay
                muted
                loop
                playsinline
              ></video>
              <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>

              <div class="absolute right-3 top-1/2 -translate-y-1/2 flex flex-col items-center gap-4 z-10">
                <button
                  type="button"
                  class="rounded-full"
                  @click="openProfile(video.user?.username)"
                  aria-label="Open author profile"
                >
                  <img
                  :src="video.user?.profile_pic_url || '/images/default-avatar.png'"
                  class="w-10 h-10 lg:w-9 lg:h-9 rounded-full border border-white/30 object-cover shadow"
                  :alt="video.user?.username || 'user'"
                  />
                </button>

                <button
                  type="button"
                  class="inline-flex flex-col items-center transition"
                  :class="video.liked_by_me ? 'text-red-500' : 'text-white'"
                  @click="toggleLike(video)"
                  aria-label="Like"
                >
                  <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-6 lg:h-6" fill="currentColor" aria-hidden="true">
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
                  <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-6 lg:h-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
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
                  <svg viewBox="0 0 24 24" class="w-7 h-7 lg:w-6 lg:h-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/>
                    <path d="M16 6l-4-4-4 4"/>
                    <path d="M12 2v14"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="w-full text-white/80 mt-1 p-3">
              <p class="font-semibold truncate">{{ video.title || 'Bez nazvu' }}</p>
              <p class="text-sm text-white/65 line-clamp-2">{{ video.description || 'Bez popisu' }}</p>
            </div>
          </div>
        </div>

        <div v-if="!loading && !videos.length" class="text-center text-white/60 py-10">
          Zatim tu nejsou zadna videa.
        </div>

        <div v-if="loading" v-for="n in 3" :key="'skeleton-' + n" class="mb-6 flex flex-col items-center gap-3 animate-pulse">
          <div class="w-full max-w-md aspect-[9/16] bg-[#1d1d21] rounded-2xl"></div>
          <div class="w-full max-w-md h-4 bg-[#1d1d21] rounded"></div>
          <div class="w-full max-w-md h-3 bg-[#1d1d21] rounded "></div>
        </div>
      </div>

      <div class="hidden w-80 lg:flex flex-col gap-6 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-center">
          <TopSearch />
        </div>

        <div class="rounded-xl bg-[#121216] border border-white/10 p-4">
          <div class="text-white font-semibold mb-3">Navrhy na sledovani</div>
          <div v-if="sidebarLoading" class="space-y-3">
            <div v-for="n in 3" :key="'suggest-skeleton-' + n" class="flex items-center">
              <div class="w-10 h-10 rounded-full bg-[#1d1d21] mr-3"></div>
              <div class="flex-1">
                <div class="h-3 bg-[#1d1d21] rounded mb-1 w-2/3"></div>
                <div class="h-2.5 bg-[#1d1d21] rounded w-1/3"></div>
              </div>
              <div class="w-16 h-7 rounded-lg bg-[#1d1d21]"></div>
            </div>
          </div>
          <div v-else-if="!suggestedUsers.length" class="text-xs text-white/50">
            Zatim zadne navrhy.
          </div>
          <div v-else class="space-y-3">
            <div v-for="user in suggestedUsers" :key="'suggest-' + user.id" class="flex items-center gap-3">
              <img :src="user.profile_pic_url || '/images/default-avatar.png'" class="w-10 h-10 rounded-full object-cover border border-white/10" />
              <button class="flex-1 text-left" @click="openProfile(user.username)">
                <div class="text-sm text-white truncate">{{ user.name }}</div>
                <div class="text-xs text-white/50">@{{ user.username }}</div>
              </button>
              <button
                type="button"
                class="text-xs rounded-lg px-2.5 py-1.5 transition"
                :class="user.is_following ? 'bg-white/10 text-white' : 'bg-pink-500 text-white hover:bg-pink-600'"
                @click="toggleFollowSuggestion(user)"
              >
                {{ user.is_following ? 'Sledujes' : 'Sledovat' }}
              </button>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-[#121216] border border-white/10 p-4">
          <div class="text-white font-semibold mb-3">Nadchazejici akce</div>
          <div v-if="sidebarLoading" class="space-y-3">
            <div v-for="n in 3" :key="'event-skeleton-' + n" class="mb-3">
              <div class="h-3 bg-[#1d1d21] rounded mb-1 w-3/4"></div>
              <div class="h-2.5 bg-[#1d1d21] rounded w-1/2"></div>
            </div>
          </div>
          <div v-else-if="!upcomingEvents.length" class="text-xs text-white/50">
            Zadne nadchazejici akce.
          </div>
          <div v-else class="space-y-3">
            <button
              v-for="event in upcomingEvents"
              :key="'event-' + event.id"
              class="w-full text-left rounded-lg bg-white/5 p-3 hover:bg-white/10 transition"
              @click="openEvent(event.id)"
            >
              <div class="text-sm text-white font-medium truncate">{{ event.title }}</div>
              <div class="text-xs text-white/60 mt-1">{{ formatDate(event.starts_at) }}</div>
              <div class="text-xs text-white/40 mt-0.5 truncate">{{ event.location || 'Bez mista' }}</div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="activeCommentItem"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 p-4"
      @click.self="closeCommentsModal"
    >
      <div class="w-full max-w-lg rounded-2xl border border-white/10 bg-[#121216]">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
          <div class="text-sm font-semibold text-white">Comments</div>
          <button class="rounded-lg px-2 py-1 text-white/70 hover:bg-white/10" @click="closeCommentsModal">Close</button>
        </div>

        <div class="max-h-[55vh] overflow-y-auto px-4 py-3 space-y-3">
          <div v-if="activeCommentItem.commentsLoading" class="text-xs text-white/50">Loading comments...</div>
          <div v-else-if="!activeCommentItem.comments?.length" class="text-xs text-white/50">No comments yet.</div>
          <div v-else class="space-y-2">
            <div v-for="comment in activeCommentItem.comments" :key="comment.id" class="rounded-xl bg-black/30 px-3 py-2.5">
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

        <form class="border-t border-white/10 p-3 flex gap-2" @submit.prevent="submitComment(activeCommentItem)">
          <input
            v-model="activeCommentItem.commentDraft"
            type="text"
            class="flex-1 rounded-lg bg-black/30 border border-white/10 px-3 py-2 text-sm text-white outline-none focus:border-pink-400"
            :placeholder="replyTargetId ? 'Write a reply...' : 'Write a comment...'"
          />
          <button type="submit" class="rounded-lg bg-pink-500 px-3 py-2 text-sm font-semibold text-white hover:bg-pink-600">
            Send
          </button>
        </form>
        <div v-if="replyTargetId" class="px-3 pb-3">
          <button class="text-xs text-white/60 hover:text-white" @click="replyTargetId = null">Cancel reply</button>
        </div>
      </div>
    </div>

    <div
      v-if="shareNotice"
      class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[75] rounded-lg bg-black/80 px-3 py-2 text-xs text-white"
    >
      {{ shareNotice }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import SideNav from '../components/SideNav.vue'
import TopSearch from '../components/TopSearch.vue'

const videos = ref([])
const loading = ref(true)
const sidebarLoading = ref(true)
const suggestedUsers = ref([])
const upcomingEvents = ref([])
const activeCommentItem = ref(null)
const replyTargetId = ref(null)
const shareNotice = ref('')
const router = useRouter()

const withVideoInteractionState = (item) => ({
  ...item,
  likes_count: item.likes_count || 0,
  comments_count: item.comments_count || 0,
  liked_by_me: !!item.liked_by_me,
  showComments: false,
  commentsLoading: false,
  comments: [],
  commentDraft: ''
})

onMounted(async () => {
  try {
    const [videosRes, usersRes, followingRes, eventsRes] = await Promise.all([
      axios.get('/api/videos'),
      axios.get('/api/users'),
      axios.get('/api/following'),
      axios.get('/api/events')
    ])
    videos.value = (Array.isArray(videosRes.data) ? videosRes.data : []).map(withVideoInteractionState)

    const followingIds = new Set((Array.isArray(followingRes.data) ? followingRes.data : []).map((u) => u.id))
    suggestedUsers.value = (Array.isArray(usersRes.data) ? usersRes.data : [])
      .filter((user) => ['performer', 'organizer'].includes(user.role))
      .map((user) => ({
        ...user,
        is_following: followingIds.has(user.id),
      }))
      .slice(0, 5)

    const now = Date.now()
    upcomingEvents.value = (Array.isArray(eventsRes.data) ? eventsRes.data : [])
      .filter((event) => {
        const startsAt = new Date(event.starts_at || 0).getTime()
        return startsAt >= now
      })
      .sort((a, b) => new Date(a.starts_at || 0).getTime() - new Date(b.starts_at || 0).getTime())
      .slice(0, 3)
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
    sidebarLoading.value = false
  }
})

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

const submitComment = async (video) => {
  if (!hasToken() || !video.commentDraft.trim()) return
  try {
    await axios.post(`/api/videos/${video.id}/comments`, {
      body: video.commentDraft.trim(),
      parent_id: replyTargetId.value || null
    })
    video.comments_count += 1
    video.commentDraft = ''
    replyTargetId.value = null
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
  activeCommentItem.value = video
  if (!video.comments?.length) {
    await fetchComments(video)
  }
}

const toggleFollowSuggestion = async (user) => {
  if (!hasToken() || !user?.username) return
  try {
    const { data } = await axios.post(`/api/users/${user.username}/follow`)
    user.is_following = !!data?.following
  } catch (err) {
    console.error(err)
  }
}

const openEvent = (eventId) => {
  if (!eventId) return
  router.push({ name: 'EventDetail', params: { id: eventId } })
}

const formatDate = (dateValue) => {
  if (!dateValue) return 'Bez data'
  const date = new Date(dateValue)
  if (Number.isNaN(date.getTime())) return 'Bez data'
  return new Intl.DateTimeFormat('cs-CZ', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

const closeCommentsModal = () => {
  activeCommentItem.value = null
  replyTargetId.value = null
}

const startReply = (comment) => {
  replyTargetId.value = comment.id
}

const activeNav = ref('home')

  const handleNavigate = (nav) => {
    activeNav.value = nav
  }
</script>

<style scoped>
.flex-1::-webkit-scrollbar {
  display: none;
}
</style>
