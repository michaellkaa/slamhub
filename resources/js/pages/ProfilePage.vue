<template>
  <div class="w-screen h-screen flex bg-[#0f0f12] text-white overflow-hidden">

    <div class=" h-full bg-[#141418] flex flex-col items-center ">
      <SideNav />
    </div>

    <div class="flex-1 flex flex-col px-12 py-10 overflow-auto">


  
  <div class="flex items-center gap-8">
    <div class="w-28 h-28 rounded-full bg-[#1d1d21] border border-white/10 shadow-xl mb-4"></div>


    <div class="flex flex-col gap-2 flex-1">
      <div class="flex items-center gap-4">
        <div class="text-blue-500">{{ user.name }} njj</div>
        <div class="h-6 w-48 bg-[#1d1d21] rounded"></div>
        <div class="h-6 w-32 bg-[#1d1d21] rounded"></div>
      </div>
      <div class="h-4 w-32 bg-[#1d1d21] rounded"></div>
      <div class="h-4 w-64 bg-[#1d1d21] rounded"></div>

      <div class="flex gap-8 mt-3">
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
      </div>
    </div>
  </div>

  <div class="flex gap-10 border-b border-white/10 pb-4">
    <div class="h-4 w-20 bg-[#1d1d21] rounded"></div>
    <div class="h-4 w-20 bg-[#1d1d21] rounded"></div>
    <div class="h-4 w-20 bg-[#1d1d21] rounded"></div>
  </div>

  <div class="grid grid-cols-3 w-[70%] gap-4 mt-8">
    <div v-for="n in 6" :key="n" class="p-3">
      <div class="w-full bg-[#1d1d21] rounded-lg aspect-[9/16]"></div>
    </div>
  </div>

</div>

<div class="w-80 border-l border-white/5 px-6 py-8 overflow-auto space-y-4">
  <div class="h-6 w-40 bg-[#1d1d21] rounded"></div>
  <div v-for="n in 4" :key="n" class="h-20 rounded-xl bg-[#1d1d21]"></div>
</div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import SideNav from '../components/SideNav.vue'

const user = ref({})
async function logout() {
  try {
    const response = await fetch('/logout', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'include',
    })

    if (!response.ok) throw new Error('Logout failed')
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout selhal', error)
  }
}


onMounted(async () => {
  const resUser = await fetch('/api/user', { credentials: 'include' })
  if (resUser.ok) {
    user.value = await resUser.json()
  }

})
</script>


