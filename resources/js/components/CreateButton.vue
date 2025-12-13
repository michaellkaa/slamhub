<template>
  <div class="fixed bottom-8 right-8 flex flex-col items-end">
    <transition name="fade">
      <div v-if="menuOpen" class="mb-2 flex flex-col gap-3">
        <button @click="onCreate('event')"
                class="bg-white text-black px-4 py-2 rounded shadow hover:bg-gray-200 transition">
          Přidat Event
        </button>
        <button @click="onCreate('post')"
                class="bg-white text-black px-4 py-2 rounded shadow hover:bg-gray-200 transition">
          Přidat Post
        </button>
        <button @click="onCreate('video')"
                class="bg-white text-black px-4 py-2 rounded shadow hover:bg-gray-200 transition">
          Přidat Video
        </button>
      </div>
    </transition>

    <button
      @click="toggleMenu"
      class="w-16 h-16 bg-[#DF68CF] text-white rounded-full flex items-center justify-center shadow-xl hover:bg-pink-600 transition"
    >
      +
    </button>
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'

const menuOpen = ref(false)
const emit = defineEmits(['create'])

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

const onCreate = (type) => {
  menuOpen.value = false
  emit('create', type)
}
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
