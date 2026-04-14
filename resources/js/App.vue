<template>
  <div>
    <router-view />
    <PwaUpdatePrompt />
  </div>
</template>


<script setup>
import PwaUpdatePrompt from './components/PwaUpdatePrompt.vue'
import SideNav from './components/SideNav.vue'
import Leaderboard from './components/Leaderboard.vue'
import League from './components/League.vue';
import SwitchTitle from './components/SwitchTitle.vue';
import { ref } from 'vue';

const activeTab = ref('leaderboard');
</script>