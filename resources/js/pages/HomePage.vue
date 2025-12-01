<template>
  <div class="w-screen h-screen flex bg-gradient-to-r from-yellow-100 via-pink-100 to-purple-100">
    <div class="h-full z-10 shadow-[4px_0_15px_rgba(0,0,0,0.2)]">
      <SideNav />
    </div>

    <div class="flex-1 flex gap-6 p-6 overflow-hidden">
      <div
        class="flex-1 rounded-xl shadow-lg p-4 overflow-y-auto max-h-[90vh] bg-yellow-50"
        style="scroll-snap-type: y mandatory;"
      >
        <h2 class="font-bold text-purple-700 mb-4">Nová videa</h2>
        <div
        v-for="n in 5"
        :key="n"
        class="mb-6 flex flex-col items-center justify-center gap-3"
        style="scroll-snap-align: start;"
        >
        <!-- 16:9 frame, centrovaný -->
        <div class="w-full max-w-md bg-gray-300 rounded-lg aspect-video"></div>
        <div class="h-4 bg-gray-300 rounded w-3/4"></div>
        <div class="h-4 bg-gray-300 rounded w-1/2"></div>
        </div>

      </div>

      <div class="w-80 flex flex-col gap-6 overflow-y-auto max-h-[90vh]">
        <div class="rounded-xl shadow-lg p-4 animate-pulse" style="background-color:#FFD6F0">
          <h2 class="font-bold text-pink-700 mb-4">Sleduj lidi</h2>
          <div v-for="n in 4" :key="'people-' + n" class="flex items-center mb-3">
            <div class="w-12 h-12 rounded-full bg-gray-300 mr-3"></div>
            <div class="flex-1">
              <div class="h-4 bg-gray-300 rounded mb-1 w-3/4"></div>
              <div class="h-3 bg-gray-300 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div class="rounded-xl shadow-lg p-4 animate-pulse" style="background-color:#C6F0FF">
          <h2 class="font-bold text-blue-700 mb-4">Blížící se akce</h2>
          <div v-for="n in 3" :key="'event-' + n" class="mb-3">
            <div class="h-4 bg-gray-300 rounded mb-1 w-3/4"></div>
            <div class="h-3 bg-gray-300 rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SideNav from '../components/SideNav.vue'
</script>
