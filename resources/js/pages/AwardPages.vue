<template>
    <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">
      <!-- SideNav -->
      <div class="lg:h-full lg:w-28 w-full fixed bottom-0 lg:static z-10">
        <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
      </div>
  
      <div class="flex-1 flex flex-col mt-4 lg:mt-28 overflow-y-auto pb-28 lg:pb-0">
        <div class="grid grid-cols-2 gap-4 w-full px-4">
          <SwitchTitle text="Liga" :isActive="activeTab === 'league'" @click="activeTab = 'league'" />
          <SwitchTitle text="Žebříček" :isActive="activeTab === 'leaderboard'" @click="activeTab = 'leaderboard'" />
        </div>
  
        <div class="mt-4 w-full">
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
  