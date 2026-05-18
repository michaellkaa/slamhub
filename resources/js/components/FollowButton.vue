<template>
  <button v-if="canFollow" @click="toggleFollow" :disabled="loading" :class="[
    'px-4 py-2 rounded transition',
    isFollowing ? 'bg-gray-700 hover:bg-gray-600' : 'bg-[#BF2679] hover:bg-[#9e2668]'
  ]">
    {{ isFollowing ? 'Sleduješ' : 'Sledovat' }}
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
  if (!canFollow.value) return
  try {
    const { data } = await axios.get(`/api/users/${props.profileUser.username}/follow-status`)
    isFollowing.value = data.is_following
  } catch (e) {
    console.error("Failed to load follow status:", e)
  }
}

const toggleFollow = async () => {
  if (!canFollow.value || loading.value) return

  loading.value = true
  try {
    const { data } = await axios.post(`/api/users/${props.profileUser.username}/follow`)
    isFollowing.value = data.following
    emit('follow-changed', data.following, data.followers_count)
  } catch (e) {
    console.error("Follow failed:", e)
  } finally {
    loading.value = false
  }
}

onMounted(loadFollowStatus)
watch(() => props.profileUser.username, loadFollowStatus)
</script>