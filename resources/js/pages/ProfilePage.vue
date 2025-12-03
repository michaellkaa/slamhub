<template>
  <div class="w-screen h-screen flex bg-[#0f0f12] text-white overflow-hidden">

    <div class=" h-full bg-[#141418] flex flex-col items-center ">
      <SideNav />
    </div>

    <div class="flex-1 flex flex-col px-12 py-10 overflow-auto">

      <div class="flex items-center gap-8">

        <div
          class="w-32 h-32 rounded-full bg-white/10 overflow-hidden border border-white/10 shadow-xl">
          <img
            v-if="!loading"
            :src="user.avatar"
            class="w-full h-full object-cover"
          />
        </div>

        <!-- Text info -->
        <div class="flex flex-col gap-2">
          <div class="flex items-center gap-4">
            <h1 class="text-3xl font-bold">Michaelka</h1>
            <button class="px-4 py-1 rounded-xl bg-white/10 border border-white/10 hover:bg-white/20 transition">
              Upravit profil
            </button>
          </div>

          <p class="text-gray-300 text-sm">@michaelka</p>
          <p class="text-gray-300 max-w-xl leading-relaxed">
            nejake bio 
          </p>

          <!-- jakoby chci tam nejaky following system? -->
          <div class="flex gap-8 mt-3 text-gray-300">
            <div><span class="font-bold text-white">25</span> sledujících</div> 
            <div><span class="font-bold text-white">42</span> sleduje</div>
            <div><span class="font-bold text-white">5</span> videí</div>
          </div>
        </div>
      </div>

      <div class="flex gap-10 mt-10 border-b border-white/10 pb-4 text-sm">
        <button class="text-white font-semibold border-b-2 border-pink-500 pb-2">Videa</button>
        <button class="text-gray-400 hover:text-white">Eventy</button>
        <button class="text-gray-400 hover:text-white">O mně</button>
      </div>

      <div class="grid grid-cols-3 w-[70%] mt-8">
                <div
        v-for="n in 5"
        :key="n"
        class="p-3"
        style="scroll-snap-align: start;"
        >
        <div class="w-full max-w-md bg-gray-300 rounded-lg aspect-[9/16] mt-6 "></div>

        </div>
      </div>

    </div>

    <div class="w-80 border-l border-white/5 px-6 py-8 overflow-auto">

      <h2 class="text-xl font-semibold mb-4">Eventy</h2>

      <div v-if="loading" class="space-y-4">
        <div class="h-20 rounded-xl bg-white/5 animate-pulse"></div>
        <div class="h-20 rounded-xl bg-white/5 animate-pulse"></div>
      </div>

      <div v-else class="flex flex-col gap-4">
        <div
          v-for="event in user.events"
          :key="event.id"
          class="bg-white/5 p-4 rounded-xl hover:bg-white/10 cursor-pointer border border-white/10 transition"
        >
          <h3 class="font-semibold text-white">{{ event.title }}</h3>
          <p class="text-gray-400 text-sm">{{ event.date }}</p>
          <p class="text-gray-300 text-sm">{{ event.location }}</p>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SideNav from '../components/SideNav.vue'

const loading = ref(true)

const user = ref({
  events: [
    { id: 1, title: "nazev akce", date: "1. 1. 2025", location: "Zlín" },
  ]
})

onMounted(() => {
  setTimeout(() => loading.value = false, 1000)
})
</script>
