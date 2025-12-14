<template>
  <div v-if="user && (user.role === 'performer' || user.role === 'organizer')" class="fixed bottom-8 right-8 flex flex-col items-end z-50">
    <transition name="slide-fade">
      <div v-if="menuOpen" class="flex flex-col items-end mb-3 gap-3">
        <button
        v-for="item in filteredMenuItems"
        :key="item.type"
        @click="onCreate(item.type)"
        class="w-40 text-white text-sm font-semibold border-b border-transparent hover:border-pink-500 hover:text-pink-400 transition-all text-right"
        >
        {{ item.label }}
        </button>

      </div>
    </transition>

    <button
      @click="toggleMenu"
      class="w-20 h-20 bg-pink-500 text-white rounded-full flex items-center justify-center shadow-2xl hover:bg-pink-600 transition-transform transform hover:scale-110"
    >
      <span class="text-3xl font-bold">+</span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const menuOpen = ref(false)

const menuItems = ref([
  { type: 'event', label: 'Přidat Event', roles: ['organizer'] },
  { type: 'post', label: 'Přidat Post', roles: ['organizer', 'performer'] },
  { type: 'video', label: 'Přidat Video', roles: ['organizer', 'performer'] },
])

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}



const onCreate = (type) => {
  menuOpen.value = false

  if(type === 'event'){
    router.push('/events/create')
  } else {
    emit('create', type)
  }
}


const emit = defineEmits(['create'])

const props = defineProps({
  user: Object
})

const filteredMenuItems = computed(() => {
  if (!props.user) return []
  return menuItems.value.filter(item => item.roles.includes(props.user.role))
})
</script>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
