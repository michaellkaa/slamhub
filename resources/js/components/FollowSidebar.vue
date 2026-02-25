<template>
    <div class="p-4 bg-[#1d1d21] rounded-xl w-72 space-y-4">
      
      <div v-if="canFollow" class="flex justify-center">
        <FollowButton :profileUser="profileUser" :authUser="authUser" />
      </div>
  
      <div v-if="showFollowers">
        <h3 class="text-white font-semibold mb-2">Followers ({{ followers.length }})</h3>
        <div class="space-y-2 max-h-40 overflow-y-auto no-scrollbar">
          <div
            v-for="user in followers"
            :key="user.id"
            class="p-2 bg-[#2a2a2f] rounded hover:bg-[#33333a] transition cursor-pointer"
          >
            {{ user.username }}
          </div>
        </div>
      </div>
  
      <div>
        <h3 class="text-white font-semibold mb-2">Following ({{ following.length }})</h3>
        <div class="space-y-2 max-h-40 overflow-y-auto no-scrollbar">
          <div
            v-for="user in following"
            :key="user.id"
            class="p-2 bg-[#2a2a2f] rounded hover:bg-[#33333a] transition cursor-pointer"
          >
            {{ user.username }}
          </div>
        </div>
      </div>
  
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue'
  import axios from 'axios'
  import FollowButton from './FollowButton.vue'
  
  const props = defineProps({
    profileUser: { type: Object, required: true },
    authUser: { type: Object, required: false }
  })
  
  const followers = ref([])
  const following = ref([])
  const showFollowers = ref(false)
  const canFollow = ref(false)
  
  const checkCanFollow = () => {
    if (!props.authUser) return false
    if (props.authUser.id === props.profileUser.id) return false
    if (!['performer', 'organizer'].includes(props.profileUser.role)) return false
    return true
  }
  
  const loadFollowers = async () => {
    if (!['performer', 'organizer'].includes(props.profileUser.role)) return
    try {
      const res = await axios.get(`/api/users/${props.profileUser.username}/followers`)
      followers.value = res.data
      showFollowers.value = true
    } catch (e) {
      console.error(e)
    }
  }
  
  const loadFollowing = async () => {
    try {
      const res = await axios.get(`/api/users/${props.profileUser.username}/following`)
      following.value = res.data
    } catch (e) {
      console.error(e)
    }
  }
  
  onMounted(() => {
    canFollow.value = checkCanFollow()
    loadFollowers()
    loadFollowing()
  })
  
  watch(() => props.profileUser, () => {
    canFollow.value = checkCanFollow()
    loadFollowers()
    loadFollowing()
  })
  </script>
  
  <style scoped>
  .no-scrollbar::-webkit-scrollbar {
    display: none;
  }
  </style>