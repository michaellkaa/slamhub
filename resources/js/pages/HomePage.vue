<template>
  <div class="w-screen h-screen flex bg-[#0f0f12]">
    <div class="h-full z-10 shadow-[4px_0_15px_rgba(0,0,0,0.2)]">
      <SideNav />
    </div>

    <div class="flex-1 flex gap-6 p-6 overflow-hidden">
        
      <div
        class="flex-1 rounded-xl  p-4 overflow-y-auto max-h-[90vh]"
        style="scroll-snap-type: y mandatory;"
      >
        <div
        v-for="n in 5"
        :key="n"
        class="mb-6 flex flex-col items-center justify-center gap-3"
        style="scroll-snap-align: start;"
        >
        <div class="w-full h-full max-w-md bg-[#1d1d21] rounded-lg aspect-[9/16] mt-5 "></div>

        </div>

      </div>

      <div class="w-80 flex flex-col gap-6 overflow-y-auto max-h-[90vh]"><div class="flex justify-center">
          <TopSearch />
        </div>
        <div class="rounded-xl shadow-lg p-4 " >
          <div class="h-4 mb-3 bg-[#1d1d21] rounded w-3/4"></div>
          <div v-for="n in 4" :key="'people-' + n" class="flex items-center mb-3">
            <div class="w-12 h-12 rounded-full bg-[#1d1d21] mr-3"></div>
            <div class="flex-1">
              <div class="h-4 bg-[#1d1d21] rounded mb-1 w-3/4"></div>
              <div class="h-3 bg-[#1d1d21] rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <div class="rounded-xl shadow-lg p-4 ">
          <div class="h-4 mb-3 bg-[#1d1d21] rounded w-3/4"></div> <!--jake akce budou zejo-->
          <div v-for="n in 3" :key="'event-' + n" class="mb-3">
            <div class="h-4 bg-[#1d1d21] rounded mb-1 w-3/4"></div>
            <div class="h-3 bg-[#1d1d21] rounded w-1/2"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import SideNav from '../components/SideNav.vue'
import TopSearch from '../components/TopSearch.vue'
</script>
