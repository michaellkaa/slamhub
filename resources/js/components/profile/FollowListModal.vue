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
              v-if="showUnfollowFor(u)"
              class="shrink-0 px-3 py-1.5 rounded-lg text-sm bg-white/10 hover:bg-white/15 text-white transition disabled:opacity-60"
              :disabled="pendingUsernames.has(u.username)"
              @click="unfollow(u.username)"
            >
              Odebrat
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
})

const emit = defineEmits(['close', 'open-profile', 'following-changed'])

const activeTab = ref(props.initialTab)
const loading = ref(true)
const error = ref('')
const followers = ref([])
const following = ref([])
const pendingUsernames = ref(new Set())

const followersCount = computed(() => followers.value.length)
const followingCount = computed(() => following.value.length)

const title = computed(() => (activeTab.value === 'followers' ? 'Sledující' : 'Sleduje'))
const activeList = computed(() => (activeTab.value === 'followers' ? followers.value : following.value))

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
  } catch (e) {
    error.value = e?.response?.data?.message || 'Nepodařilo se načíst seznam.'
  } finally {
    loading.value = false
  }
}

const showUnfollowFor = (u) => {
  if (!props.canUnfollow) return false
  if (activeTab.value !== 'following') return false
  return !!u?.username
}

const unfollow = async (targetUsername) => {
  if (!targetUsername || pendingUsernames.value.has(targetUsername)) return
  pendingUsernames.value.add(targetUsername)
  error.value = ''

  const prev = following.value
  following.value = following.value.filter((u) => u.username !== targetUsername)
  emit('following-changed', following.value.length)

  try {
    const { data } = await axios.post(`/api/users/${targetUsername}/follow`)
    if (data?.following !== false) {
      // API toggles; we expect "false" for an unfollow in this modal.
      following.value = prev
      emit('following-changed', following.value.length)
      error.value = 'Nepodařilo se odebrat sledování.'
    }
  } catch (e) {
    following.value = prev
    emit('following-changed', following.value.length)
    error.value = e?.response?.data?.message || 'Nepodařilo se odebrat sledování.'
  } finally {
    pendingUsernames.value.delete(targetUsername)
  }
}

onMounted(load)
watch(() => props.username, load)
</script>
