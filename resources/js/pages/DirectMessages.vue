<template>
    <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">
      <div class="lg:h-full lg:w-28 w-full h-36 fixed bottom-0 lg:static z-10">
        <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
      </div>
  
      <div class="flex-1 flex flex-col  overflow-y-auto pb-28 lg:pb-0">
        <div class="flex w-full h-full overflow-hidden">
  
  <div class="w-72 bg-[#141418] border-r border-[#1f1f22] flex flex-col">
    <div class="h-16 border-b border-[#1f1f22] flex items-center px-4">
      <div class="h-6 w-40 bg-[#1d1d21] rounded "></div>

    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
      <div v-for="n in 10" :key="n" class="flex items-center space-x-3">
        <div class="h-12 w-12 rounded-full bg-[#1d1d21]   "></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 w-40 bg-[#1d1d21] rounded "></div>
          <div class="h-3 w-28 bg-[#1d1d21] rounded  "></div>
        </div>
      </div>
    </div>
  </div>


  <div class="flex-1 flex flex-col">
    <div class="h-16 border-b border-[#1f1f22] flex items-center px-6 space-x-4">
      <div class="h-12 w-12 rounded-full bg-[#1d1d21]   "></div>
      <div>
        <div class="h-4 w-48 bg-[#1d1d21] rounded    mb-2"></div>
        <div class="h-3 w-32 bg-[#1d1d21] rounded   "></div>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-6 space-y-6">
      <div class="flex space-x-3 items-start">
        <div class="h-10 w-10 rounded-full bg-[#1d1d21] "></div>
        <div class="space-y-2">
          <div class="h-4 w-72 bg-[#1d1d21] rounded"></div>
          <div class="h-4 w-52 bg-[#1d1d21] rounded "></div>
        </div>
      </div>

      <div class="flex justify-end">
        <div class="space-y-2">
          <div class="h-4 w-64 bg-[#1d1d21] rounded "></div>
          <div class="h-4 w-40 bg-[#1d1d21] rounded "></div>
        </div>
      </div>

      <div class="flex space-x-3 items-start">
        <div class="h-10 w-10 rounded-full bg-[#1d1d21] "></div>
        <div class="space-y-2">
          <div class="h-4 w-60 bg-[#1d1d21] rounded "></div>
          <div class="h-4 w-48 bg-[#1d1d21] rounded"></div>
        </div>
      </div>
    </div>

    <div class="h-20 border-t border-[#1f1f22] flex items-center px-6 space-x-4">
      <div class="h-12 flex-1 bg-[#1d1d21] rounded-lg"></div>
      <div class="h-12 w-24 bg-[#1d1d21] rounded-lg"></div>
    </div>
  </div>

</div>
      </div>
    </div>
  </template>
  
  <script setup>
  import SideNav from '../components/SideNav.vue'
  
  import { ref } from 'vue'
  
  const activeNav = ref('award')

  </script>
