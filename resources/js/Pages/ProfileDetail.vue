<template>
  <div class="w-screen h-screen flex bg-[#0f0f12] text-white overflow-hidden">

    <div class="h-full bg-[#141418] flex flex-col items-center">
      <SideNav />
    </div>

    <div class="flex-1 flex flex-col px-12 py-10 overflow-auto no-scrollbar">

      <div v-if="user" class="flex flex-col gap-4">
        <div class="flex items-center gap-8">
          <img
            :src="user.profile_pic_url"
            class="w-28 h-28 rounded-full object-cover border border-white/10 shadow-xl cursor-pointer"
            @click="triggerUpload"
            :class="{ 'cursor-not-allowed opacity-60': !isOwnProfile }"
          />

          <input
            type="file"
            ref="fileInput"
            class="hidden"
            accept="image/*"
            @change="uploadPhoto"
          />

          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-4">
              <div class="font-bold text-xl">{{ user.name }}</div>
              <button
                v-if="isOwnProfile"
                @click="goToSettings"
                class="p-2 rounded-md bg-[#1d1d21] hover:bg-[#2a2a30] transition"
                title="Settings"
                aria-label="Open settings"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="w-4 h-4 text-white"
                >
                  <circle cx="12" cy="12" r="3"></circle>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-.33-1A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1-.33H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1-.33A1.65 1.65 0 0 0 4.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .33-1V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 .33 1A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 8c0 .39.14.76.4 1.04.27.28.64.44 1.04.46H21a2 2 0 1 1 0 4h-.09c-.4.02-.77.18-1.04.46-.26.28-.4.65-.4 1.04z"></path>
                </svg>
              </button>

              <FollowButton
                v-if="loggedUser && !isOwnProfile"
                :profileUser="user"
                :authUser="loggedUser"
                @follow-changed="handleFollow"
              />
              
            </div>

            <div class="text-white/40 text-sm">@{{ user.username }}</div>

            <div class="flex gap-6 mt-2 text-white/70 text-sm">
              <div>
                <span class="font-semibold">{{ user.followers_count || 0 }}</span> sledující
              </div>
              <div>
                <span class="font-semibold">{{ user.following_count || 0 }}</span> sleduje
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="uploadError"
        class="mt-3 max-w-lg rounded-md border border-red-500/40 bg-red-500/10 text-red-300 text-sm px-4 py-3"
      >
        {{ uploadError }}
      </div>

      <div v-else class="flex items-center gap-8 animate-pulse">
        <div class="w-28 h-28 rounded-full bg-[#1d1d21] border border-white/10"></div>
        <div class="flex flex-col gap-3 flex-1">
          <div class="h-6 w-48 bg-[#1d1d21] rounded"></div>
          <div class="h-4 w-40 bg-[#1d1d21] rounded"></div>
        </div>
      </div>

      <div class="border-b border-white/10 pb-4 mt-8">
        <div v-if="!user" class="flex gap-10">
          <div v-for="n in 4" :key="n" class="h-4 w-16 bg-[#1d1d21] rounded"></div>
        </div>

        <div v-else class="flex gap-10">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="flex flex-col items-center gap-2"
          >
            <img
              :src="tab.icon"
              class="w-6 h-6 transition"
              :class="activeTab === tab.key ? 'opacity-100' : 'opacity-40'"
            />
            <div
              v-if="activeTab === tab.key"
              class="w-full h-[2px] bg-pink-500 rounded"
            ></div>
          </button>
        </div>
      </div>

      <div class="mt-8" v-if="user">
        <component
          :is="activeComponent"
          :username="user.username"
        />
      </div>

    </div>

    <div
      v-if="!user"
      class="w-80 border-l border-white/5 px-6 py-8 overflow-auto space-y-4"
    >
      <div class="h-6 w-40 bg-[#1d1d21] rounded"></div>
      <div v-for="n in 4" :key="n" class="h-20 rounded-xl bg-[#1d1d21]"></div>
    </div>

    <CreateButton
      v-if="isOwnProfile"
      :user="user"
      @create="handleCreate"
    />

  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import axios from 'axios'

import SideNav from '../components/SideNav.vue'
import VideosTab from '../components/profile/VideosTab.vue'
import PostsTab from '../components/profile/PostsTab.vue'
import AwardsTab from '../components/profile/AwardsTab.vue'
import EventsTab from '../components/profile/EventsTab.vue'
import CreateButton from '../components/CreateButton.vue'
import FollowButton from '../components/FollowButton.vue'

const route = useRoute()
const user = ref(null)
const loggedUser = ref(null)
const fileInput = ref(null)
const uploadError = ref('')
const activeTab = ref('videos')
const router = useRouter()

const loadProfile = async () => {
  try {
    const token = localStorage.getItem('token');

    if (!token) {
      router.push('/login');
      return;
    }

    axios.defaults.headers.common.Authorization = `Bearer ${token}`;

    const { data: me } = await axios.get('/api/me');
    loggedUser.value = me;

    const username = route.params.username;

    if (username && username !== loggedUser.value.username) {
      const { data } = await axios.get(`/api/users/${username}`);
      user.value = data;
    } else {
      user.value = { ...loggedUser.value };
    }

    if (user.value) {
      user.value.followers_count = user.value.followers_count || 0;
      user.value.following_count = user.value.following_count || 0;
      user.value.is_following = user.value.is_following || false;
    }
  } catch (err) {
    console.error('Chyba při načítání profilu:', err);
    router.push('/login');
  }
};

const handleFollow = (following, count) => {
  if (!user.value) return
  user.value.is_following = following
  user.value.followers_count = count
}
onMounted(loadProfile)
watch(() => route.params.username, loadProfile)

const isOwnProfile = computed(() => {
  return loggedUser.value && user.value?.id === loggedUser.value.id
})

const tabs = computed(() => {
  if (!user.value) return []

  const baseTabs = [
    { key: 'videos', icon: '/icons/video.png' },
    { key: 'posts', icon: '/icons/edit.png' },
    { key: 'events', icon: '/icons/calendar.png' }
  ]

  if (user.value.role === 'performer') {
    baseTabs.splice(2, 0, { key: 'awards', icon: '/icons/award.png' })
  }

  return baseTabs
})

const activeComponent = computed(() => {
  return {
    videos: VideosTab,
    posts: PostsTab,
    awards: AwardsTab,
    events: EventsTab
  }[activeTab.value]
})

const triggerUpload = () => {
  if (!isOwnProfile.value) return
  fileInput.value.click()
}

const uploadPhoto = async (e) => {
  if (!isOwnProfile.value) return
  uploadError.value = ''
  const file = e.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('photo', file)

  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    axios.defaults.headers.common.Authorization = `Bearer ${token}`

    const { data } = await axios.post('/api/profile/photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (data?.profile_pic_url && user.value) {
      user.value.profile_pic_url = data.profile_pic_url
    }
  } catch (err) {
    const backendMessage = err.response?.data?.message
      || err.response?.data?.errors?.photo?.[0]
      || err.response?.data?.errors?.profile_picture?.[0]
      || err.message
    uploadError.value = backendMessage.includes('kilobytes')
      ? 'Photo is too large. Maximum size is 10 MB.'
      : backendMessage
    console.error('Photo upload failed:', backendMessage, err.response?.data)
  } finally {
    if (fileInput.value) fileInput.value.value = ''
  }
}


const handleCreate = (type) => {
  console.log('Create clicked:', type)
}

const goToSettings = () => {
  router.push('/settings')
}
</script>