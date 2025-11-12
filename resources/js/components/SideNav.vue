<template>
    <div
      class="bg-[#BF2679] z-10 flex justify-center items-center
             w-screen h-36 
             lg:static lg:w-28 lg:flex-col lg:h-screen
             "
    >
      <div class="flex lg:flex-col flex-row justify-center items-center gap-10 lg:gap-16 p-5">
        <!-- HOME -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="feather feather-home cursor-pointer"
          :class="activeNav === 'home' ? 'stroke-[#E1FF00]' : 'stroke-[#FFF7CC]'"
          @click="$emit('navigate', 'home')">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
  
        <!-- EVENT -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="feather feather-calendar cursor-pointer"
          :class="activeNav === 'event' ? 'stroke-[#E1FF00]' : 'stroke-[#FFF7CC]'"
          @click="$emit('navigate', 'event')">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
  
        <!-- AWARD -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="feather feather-award cursor-pointer"
          :class="activeNav === 'award' ? 'stroke-[#E1FF00]' : 'stroke-[#FFF7CC]'"
          @click="$emit('navigate', 'award')">
          <circle cx="12" cy="8" r="7"/>
          <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
        </svg>
  
        <!-- SEND -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="feather feather-send cursor-pointer"
          :class="activeNav === 'send' ? 'stroke-[#E1FF00]' : 'stroke-[#FFF7CC]'"
          @click="$emit('navigate', 'send')">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
  
        <!-- USER -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="feather feather-user cursor-pointer"
          :class="activeNav === 'user' ? 'stroke-[#E1FF00]' : 'stroke-[#FFF7CC]'"
          @click="$emit('navigate', 'user')">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({
    activeNav: String
  });
  defineEmits(['navigate']);
  </script>
  