<template>
  <button
    v-if="canFollow"
    @click="toggleFollow"
    :disabled="loading"
    :class="[
      'px-4 py-2 rounded transition',
      isFollowing ? 'bg-gray-700 hover:bg-gray-600' : 'bg-[#BF2679] hover:bg-[#9e2668]'
    ]"
  >
    {{ isFollowing ? 'Unfollow' : 'Follow' }}
  </button>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  profileUser: { type: Object, required: true },
  authUser: { type: Object, required: false }
})

const emit = defineEmits(['follow-changed'])
const isFollowing = ref(false)
const loading = ref(false)

const canFollow = computed(() => {
  if (!props.authUser) return false
  if (props.authUser.id === props.profileUser.id) return false
  if (!['performer', 'organizer'].includes(props.profileUser.role)) return false
  return true
})


const loadFollowStatus = async () => {
  if (!props.authUser) return
  if (!props.profileUser?.username) return
  if (!canFollow.value) return

  try {
    const { data } = await axios.get(`/api/users/${props.profileUser.username}/follow-status`)
    isFollowing.value = data.is_following
  } catch (e) {
    if (e.response?.status === 401) {
      console.warn("User not authenticated")
      localStorage.removeItem('token')
    } else {
      console.error("Failed to load follow status:", e)
    }
  }
}


/*const toggleFollow = async () => {
  if (loading.value) return
  loading.value = true
  try {
    const res = await axios.post(`/api/users/${props.profileUser.username}/follow`)
    isFollowing.value = res.data.following
    emit('follow-changed', res.data.following)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const toggleFollow = async () => {
  const res = await axios.post(`/api/users/${props.profileUser.username}/follow`)

  isFollowing.value = res.data.following

  emit('follow-changed', res.data.following)
}*/

const toggleFollow = async () => {
  if (loading.value) return
  if (!props.authUser) return

  loading.value = true

  try {
    const res = await axios.post(`/api/users/${props.profileUser.username}/follow`)

    isFollowing.value = res.data.following
    emit('follow-changed', res.data.following)

  } catch (e) {
    console.error('Follow failed:', e)
  } finally {
    loading.value = false
  }
}

onMounted(loadFollowStatus)
watch(() => props.profileUser.username, loadFollowStatus)
</script>