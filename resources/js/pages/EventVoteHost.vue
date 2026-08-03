<template>
  <div class="min-h-screen bg-app text-app px-4 py-6 flex justify-center">
    <div class="w-full max-w-2xl">
      <div class="flex items-center justify-between mb-6">
        <button @click="$router.back()" class="text-pink-500 hover:underline text-lg">
          ←
        </button>
        <div class="text-sm text-app-muted">
          Host panel
        </div>
      </div>

      <VotingHostPanel :event-id="eventId" />
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import VotingHostPanel from '../components/Organizer/VotingHostPanel.vue'

const route = useRoute()
const eventId = route.params.id
</script>
