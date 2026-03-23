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

    <div class="w-80 border-l border-white/5 px-6 py-8 overflow-auto space-y-4">
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
import { useRoute } from 'vue-router'
import { useRouter } from 'vue-router'

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
const activeTab = ref('videos')
const router = useRouter()

const loadProfile = async () => {
  try {
    const token = localStorage.getItem('token')

    if (!token) {
      router.push('/login')
      return
    }

    axios.defaults.headers.common.Authorization = `Bearer ${token}`

    const { data } = await axios.get('/api/me')
    loggedUser.value = data

    const username = route.params.username

    if (username && username !== loggedUser.value.username) {
      const res = await axios.get(`/api/users/${username}`)
      user.value = res.data
    } else {
      user.value = loggedUser.value
    }

  } catch (err) {
    console.error(err)

    router.push('/login')
  }
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
  const file = e.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('photo', file)

  try {
    await axios.post('/api/profile/photo', formData)
    loadProfile()
  } catch (err) {
    console.error('Photo upload failed:', err)
  }
}

const handleFollow = (following) => {
  const currentCount = user.value?.followers_count || 0
  user.value = {
    ...user.value,
    followers_count: Math.max(0, following ? currentCount + 1 : currentCount - 1)
  }
}

const handleCreate = (type) => {
  console.log('Create clicked:', type)
}

const updateFollowers = (following) => {
  if (following) {
    user.value.followers_count++
  } else {
    user.value.followers_count--
  }
}
</script>