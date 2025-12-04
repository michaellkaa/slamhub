<template>
    <div class="bg-[#0f0f12] w-screen min-h-screen flex flex-col lg:flex-row overflow-hidden">
      <!-- SideNav -->
      <div class="lg:h-full lg:w-28 w-full h-36 fixed bottom-0 lg:static z-10">
        <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
      </div>
  
      <!-- Main Content -->
      <div class="flex-1 flex flex-col mt-4 lg:mt-28 overflow-y-auto pb-28 lg:pb-0 ">
        <div class="w-full flex justify-center px-4">
          <div class="max-w-5xl w-full space-y-8">
  
            <!-- Skeleton Header -->
            <section class="mt-4 mb-2 text-center space-y-3">
              <div class="mx-auto w-48 h-6 bg-[#2a2a2e] rounded"></div>
              <div class="mx-auto w-72 h-4 bg-[#2a2a2e] rounded"></div>
            </section>
  
            <!-- Skeleton Event Grid -->
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  
              <!-- Skeleton Card -->
              <div class="bg-[#1a1a1d] border border-[#26262b] rounded-xl overflow-hidden flex flex-col">
                <div class="h-28 bg-[#2a2a2e]"></div>
                <div class="p-4 space-y-3 flex-1">
                  <div class="w-3/4 h-4 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/2 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/3 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-full h-8 bg-[#2a2a2e] rounded mt-4"></div>
                </div>
              </div>
  
              <div class="bg-[#1a1a1d] border border-[#26262b] rounded-xl overflow-hidden flex flex-col">
                <div class="h-28 bg-[#2a2a2e]"></div>
                <div class="p-4 space-y-3 flex-1">
                  <div class="w-3/4 h-4 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/2 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/3 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-full h-8 bg-[#2a2a2e] rounded mt-4"></div>
                </div>
              </div>
  
              <div class="bg-[#1a1a1d] border border-[#26262b] rounded-xl overflow-hidden flex flex-col">
                <div class="h-28 bg-[#2a2a2e]"></div>
                <div class="p-4 space-y-3 flex-1">
                  <div class="w-3/4 h-4 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/2 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-1/3 h-3 bg-[#2a2a2e] rounded"></div>
                  <div class="w-full h-8 bg-[#2a2a2e] rounded mt-4"></div>
                </div>
              </div>
  
            </section>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import SideNav from '../components/SideNav.vue'
  import { ref } from 'vue'
  
  const activeNav = ref('award')
  
  function handleNavigate(nav) {
    activeNav.value = nav
  }
  </script>
  