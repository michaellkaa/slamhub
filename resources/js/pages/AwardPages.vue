<template>
  <div class="bg-[#1C1C1C] w-screen h-screen flex overflow-hidden">
    <!-- boční panel -->
    <div class="h-full z-10 shadow-[4px_0_15px_rgba(0,0,0,0.4)]">
      <SideNav />
    </div>

    <div class="flex-1 flex flex-col mt-28 overflow-y-auto">
      <!-- Nadpisy -->
      <div class="grid grid-cols-2 gap-4 w-full px-4">
        <SwitchTitle
          text="Liga"
          :isActive="activeTab === 'league'"
          @click="activeTab = 'league'"
        />
        <SwitchTitle
          text="Žebříček"
          :isActive="activeTab === 'leaderboard'"
          @click="activeTab = 'leaderboard'"
        />
      </div>

      <!-- Obsah -->
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
</script>
