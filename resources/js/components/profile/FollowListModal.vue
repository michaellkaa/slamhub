<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    @keydown.esc.prevent="emit('close')"
  >
    <button
      class="absolute inset-0 bg-black/60"
      aria-label="Close"
      @click="emit('close')"
    />

    <div class="relative w-[92vw] max-w-lg rounded-2xl border border-white/10 bg-[#121216] shadow-2xl overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
        <div class="text-white font-semibold">
          {{ title }}
        </div>
        <button
          class="p-2 rounded-lg hover:bg-white/5 transition text-white/70 hover:text-white"
          aria-label="Close"
          @click="emit('close')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               class="w-4 h-4">
            <path d="M18 6L6 18"></path>
            <path d="M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="flex border-b border-white/10">
        <button
          class="flex-1 px-4 py-3 text-sm transition"
          :class="activeTab === 'followers' ? 'text-white bg-white/5' : 'text-white/60 hover:text-white'"
          @click="activeTab = 'followers'"
        >
          Sledující
          <span class="text-white/40">({{ followersCount }})</span>
        </button>
        <button
          class="flex-1 px-4 py-3 text-sm transition"
          :class="activeTab === 'following' ? 'text-white bg-white/5' : 'text-white/60 hover:text-white'"
          @click="activeTab = 'following'"
        >
          Sleduje
          <span class="text-white/40">({{ followingCount }})</span>
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto">
        <div v-if="loading" class="p-4 space-y-3">
          <div v-for="n in 6" :key="n" class="h-12 rounded-xl bg-white/5 animate-pulse" />
        </div>

        <div v-else class="p-2">
          <div v-if="activeList.length === 0" class="p-6 text-center text-white/50 text-sm">
            Nic tu není.
          </div>

          <div
            v-for="u in activeList"
            :key="u.id"
            class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-white/5 transition"
          >
            <button
              class="flex items-center gap-3 text-left min-w-0 flex-1"
              @click="emit('open-profile', u.username)"
              :aria-label="`Open profile ${u.username}`"
            >
              <img
                :src="u.profile_pic_url || '/images/default-avatar.png'"
                class="w-10 h-10 rounded-full object-cover border border-white/10"
                alt=""
              />
              <div class="min-w-0">
                <div class="text-white text-sm font-medium truncate">
                  {{ u.name || u.username }}
                </div>
                <div class="text-white/50 text-xs truncate">
                  @{{ u.username }}
                </div>
              </div>
            </button>

            <button
              v-if="showActionFor(u)"
              class="shrink-0 px-3 py-1.5 rounded-lg text-sm bg-white/10 hover:bg-white/15 text-white transition disabled:opacity-60"
              :disabled="pendingUsernames.has(u.username)"
              @click="toggleRelationship(u)"
            >
              {{ actionLabel(u) }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="error" class="px-4 py-3 border-t border-white/10 text-sm text-red-300 bg-red-500/10">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  username: { type: String, required: true },
  initialTab: { type: String, default: 'following' }, // 'followers' | 'following'
  canUnfollow: { type: Boolean, default: false },
  authUser: { type: Object, default: null },
})

const emit = defineEmits(['close', 'open-profile', 'following-changed'])

const activeTab = ref(props.initialTab)
const loading = ref(true)
const error = ref('')
const followers = ref([])
const following = ref([])
const pendingUsernames = ref(new Set())
const followedUsernames = ref(new Set())

const followersCount = computed(() => followers.value.length)
const followingCount = computed(() => following.value.length)

const title = computed(() => (activeTab.value === 'followers' ? 'Sledující' : 'Sleduje'))

const rolePriority = (role) => {
  if (role === 'organizer') return 0
  if (role === 'performer') return 1
  return 2
}

const sortByPriority = (list) => {
  return [...list].sort((a, b) => {
    const roleDiff = rolePriority(a?.role) - rolePriority(b?.role)
    if (roleDiff !== 0) return roleDiff
    return String(a?.name || a?.username || '').localeCompare(String(b?.name || b?.username || ''))
  })
}

const activeList = computed(() => {
  const list = activeTab.value === 'followers' ? followers.value : following.value
  return sortByPriority(list)
})

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const [f1, f2] = await Promise.all([
      axios.get(`/api/users/${props.username}/followers`),
      axios.get(`/api/users/${props.username}/following`),
    ])
    followers.value = Array.isArray(f1.data) ? f1.data : []
    following.value = Array.isArray(f2.data) ? f2.data : []

    if (props.authUser?.username) {
      const ownFollowing = await axios.get(`/api/users/${props.authUser.username}/following`)
      const ownFollowingList = Array.isArray(ownFollowing.data) ? ownFollowing.data : []
      followedUsernames.value = new Set(
        ownFollowingList
          .map((u) => u?.username)
          .filter(Boolean)
      )
    } else {
      followedUsernames.value = new Set()
    }
  } catch (e) {
    error.value = e?.response?.data?.message || 'Nepodařilo se načíst seznam.'
  } finally {
    loading.value = false
  }
}

const isFollowableRole = (u) => ['performer', 'organizer'].includes(u?.role)

const showActionFor = (u) => {
  if (!u?.username) return false
  if (props.authUser?.id && props.authUser.id === u.id) return false

  if (activeTab.value === 'following') {
    return props.canUnfollow
  }

  if (activeTab.value === 'followers') {
    return !!props.authUser && isFollowableRole(u)
  }

  return false
}

const actionLabel = (u) => {
  if (activeTab.value === 'following') return 'Odebrat'
  return followedUsernames.value.has(u.username) ? 'Odebrat' : 'Sledovat'
}

const toggleRelationship = async (targetUser) => {
  const targetUsername = targetUser?.username
  if (!targetUsername || pendingUsernames.value.has(targetUsername)) return
  pendingUsernames.value.add(targetUsername)
  error.value = ''

  const wasFollowing = followedUsernames.value.has(targetUsername)
  const prevFollowingList = following.value
  const prevFollowedSet = new Set(followedUsernames.value)

  if (activeTab.value === 'following' && props.canUnfollow) {
    following.value = following.value.filter((u) => u.username !== targetUsername)
    emit('following-changed', following.value.length)
  }

  if (wasFollowing) {
    followedUsernames.value.delete(targetUsername)
  } else {
    followedUsernames.value.add(targetUsername)
  }

  try {
    const { data } = await axios.post(`/api/users/${targetUsername}/follow`)
    if (typeof data?.following !== 'boolean') {
      throw new Error('Invalid follow response')
    }

    if (data.following !== !wasFollowing) {
      following.value = prevFollowingList
      followedUsernames.value = prevFollowedSet
      if (activeTab.value === 'following' && props.canUnfollow) {
        emit('following-changed', following.value.length)
      }
      error.value = 'Nepodařilo se změnit sledování.'
      return
    }
  } catch (e) {
    following.value = prevFollowingList
    followedUsernames.value = prevFollowedSet
    if (activeTab.value === 'following' && props.canUnfollow) {
      emit('following-changed', following.value.length)
    }
    error.value = e?.response?.data?.message || 'Nepodařilo se změnit sledování.'
  } finally {
    pendingUsernames.value.delete(targetUsername)
  }
}

onMounted(load)
watch(() => props.username, load)
</script>
