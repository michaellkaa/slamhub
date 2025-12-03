<template>
    <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">
      <!-- SideNav -->
      <div class="lg:h-full lg:w-28 w-full h-36 fixed bottom-0 lg:static z-10">
        <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
      </div>
  
      <div class="flex-1 flex flex-col mt-4 lg:mt-28 overflow-y-auto pb-28 lg:pb-0">

      </div>
    </div>
  </template>
  
  <script setup>
  import SideNav from '../components/SideNav.vue'
  
  import { ref } from 'vue'
  
  const activeNav = ref('award')

  </script>
  