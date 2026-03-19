<template>
    <button
      v-if="canFollow"
      @click="toggleFollow"
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

const isFollowing = ref(false)
const loading = ref(false)

const canFollow = computed(() => {
  if (!props.authUser) return false
  if (props.authUser.id === props.profileUser.id) return false
  if (!['performer', 'organizer'].includes(props.profileUser.role)) return false
  return true
})

const loadFollowStatus = async () => {
  if (!props.authUser || !canFollow.value) return
  try {
    const res = await axios.get(`/api/users/${props.profileUser.username}`)
    isFollowing.value = res.data.is_following
  } catch (e) {
    console.error(e)
  }
}

const emit = defineEmits(['follow-changed'])

const toggleFollow = async () => {
  try {
    const res = await axios.post(`/api/users/${props.profileUser.username}/follow`)

    isFollowing.value = res.data.following

    emit('follow-changed', res.data.following)

  } catch (e) {
    console.error(e)
  }
}
onMounted(loadFollowStatus)

watch(() => props.profileUser.username, loadFollowStatus)
</script>