<template>
  <h1 @click="$emit('click')" :class="[
    'cursor-pointer uppercase text-[21px] lg:text-xl pb-2 transition-colors duration-300 relative text-center font-bold tracking-wider mt-28 lg:mt-0',
    isActive ? 'text-[#E1FF00]' : 'text-[#bf267a]'
  ]">
    {{ text }}
    <span
      class="absolute bottom-0 left-0 h-0.5 w-full bg-[#E1FF00] transform scale-x-0 origin-left transition-transform duration-600 "
      :class="isActive ? 'scale-x-100' : 'scale-x-0'"></span>
  </h1>
</template>

<script setup>
defineProps({
  text: String,
  isActive: Boolean
});
</script>