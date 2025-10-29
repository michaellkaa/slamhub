<template>
  <div class="bg-[#1C1C1C] w-screen h-screen flex overflow-hidden">
    <div class="h-full z-10 shadow-[4px_0_15px_rgba(0,0,0,0.4)]">
      <SideNav />
    </div>

    <div class="grid grid-cols-2 mt-28 overflow-y-auto justify-center w-screen gap-4">
      <!-- Liga -->
      <div class="flex flex-col col-span-2">
        <SwitchTitle
          text="Liga"
          :isActive="activeTab === 'league'"
          @click="activeTab = 'league'"
        />
        <div v-if="activeTab === 'league'" class="w-full">
          <League />
        </div>
      </div>

      <!-- Žebříček -->
      <div class="flex flex-col col-span-2">
        <SwitchTitle
          text="Žebříček"
          :isActive="activeTab === 'leaderboard'"
          @click="activeTab = 'leaderboard'"
        />
        <div v-if="activeTab === 'leaderboard'" class="w-full">
          <Leaderboard />
        </div>
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
</script>
