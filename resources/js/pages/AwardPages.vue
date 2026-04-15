<template>
    <div class="bg-[#0f0f12] w-screen min-h-screen flex flex-col md:flex-row">
      <!-- SideNav -->
      <div class="md:h-full md:w-28 w-full fixed bottom-0 md:static z-10">
        <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
      </div>
  
      <div class="flex-1 flex flex-col mt-4 md:mt-20 pb-28 md:pb-8">
        <div class="grid grid-cols-2 gap-4 w-full max-w-4xl mx-auto px-4 md:px-6">
          <SwitchTitle text="Liga" :isActive="activeTab === 'league'" @click="activeTab = 'league'" />
          <SwitchTitle text="Žebříček" :isActive="activeTab === 'leaderboard'" @click="activeTab = 'leaderboard'" />
        </div>
  
        <div class="mt-6 w-full">
          <League v-if="activeTab === 'league'" />
          <Leaderboard v-if="activeTab === 'leaderboard'" />
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import SideNav from '../components/SideNav.vue'
  import Leaderboard from '../components/Leaderboard.vue'
  import League from '../components/League.vue'
  import SwitchTitle from '../components/SwitchTitle.vue'
  
  import { ref } from 'vue'
  
  const activeTab = ref('leaderboard')
  const activeNav = ref('award')
  
  const handleNavigate = (nav) => {
    activeNav.value = nav
  }
  </script>
  