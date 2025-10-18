<template>
    <h1
      @click="$emit('click')"
      :class="[
        'cursor-pointer uppercase text-lg pb-2 transition-colors duration-300 relative text-center',
        isActive ? 'text-yellow-400' : 'text-white'
      ]"
    >
      {{ text }}
      <span
      class="absolute bottom-0 left-0 h-0.5 w-full bg-yellow-400 transform scale-x-0 origin-left transition-transform duration-600 "
      :class="isActive ? 'scale-x-100' : 'scale-x-0'"
    ></span>
    </h1>
  </template>
  
  <script setup>
  defineProps({
    text: String,
    isActive: Boolean
  });
  </script>
  