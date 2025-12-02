<template>
  <div class="w-screen h-screen flex bg-[#0f0f12] text-white overflow-hidden">

    <!-- LEFT SIDEBAR -->
    <div class="w-20 h-full bg-[#141418] border-r border-white/5 flex flex-col items-center py-6 gap-8">
      <SideNav />
    </div>

    <!-- MAIN MIDDLE CONTENT -->
    <div class="flex-1 flex flex-col px-12 py-10 overflow-auto">

      <!-- HEADER SECTION -->
      <div class="flex items-center gap-8">

        <!-- Avatar -->
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
            <h1 class="text-3xl font-bold">{{ user.name }}</h1>
            <button class="px-4 py-1 rounded-xl bg-white/10 border border-white/10 hover:bg-white/20 transition">
              Upravit profil
            </button>
          </div>

          <p class="text-gray-300 text-sm">@{{ user.username }}</p>
          <p class="text-gray-300 max-w-xl leading-relaxed">
            {{ user.bio }}
          </p>

          <!-- Stats -->
          <div class="flex gap-8 mt-3 text-gray-300">
            <div><span class="font-bold text-white">{{ user.followers }}</span> sledujících</div>
            <div><span class="font-bold text-white">{{ user.following }}</span> sleduje</div>
            <div><span class="font-bold text-white">5</span> videí</div>
          </div>
        </div>
      </div>

      <!-- TABS -->
      <div class="flex gap-10 mt-10 border-b border-white/10 pb-4 text-sm">
        <button class="text-white font-semibold border-b-2 border-pink-500 pb-2">Videa</button>
        <button class="text-gray-400 hover:text-white">Eventy</button>
        <button class="text-gray-400 hover:text-white">O mně</button>
      </div>

      <!-- VIDEOS GRID -->
      <div class="grid grid-cols-5  mt-8">
                <div
        v-for="n in 5"
        :key="n"
        class="mb-6 flex flex-col items-center justify-center "
        style="scroll-snap-align: start;"
        >
        <!-- 16:9 frame, centrovaný -->
        <div class="w-[50%]  max-w-md bg-gray-300 rounded-lg aspect-[9/16] mt-6 "></div>

        </div>
      </div>

    </div>

    <!-- RIGHT INFO PANEL -->
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
  name: "Michaelka",
  username: "michaelka",
  avatar: "/uploads/avatar.jpg",

  bio: "Tvořím, programuji a žiju momentem. A sbírám eventy jako Pokémony.",

  followers: 493,
  following: 201,


  events: [
    { id: 1, title: "Creative Hill MeetUp", date: "12. 1. 2025", location: "Zlín" },
    { id: 2, title: "Developers Night", date: "20. 1. 2025", location: "Brno" }
  ]
})

onMounted(() => {
  setTimeout(() => loading.value = false, 1000)
})
</script>
