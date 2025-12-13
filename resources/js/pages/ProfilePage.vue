<template>
  <div class="w-screen h-screen flex bg-[#0f0f12] text-white overflow-hidden">

    <div class="h-full bg-[#141418] flex flex-col items-center">
      <SideNav />
    </div>

    <div class="flex-1 flex flex-col px-12 py-10 overflow-auto">

      <div v-if="user" class="flex items-center gap-8">
        <img
            :src="user.profile_pic_url"
            class="w-28 h-28 rounded-full object-cover border border-white/10 shadow-xl mb-4 cursor-pointer"
            @click="triggerUpload"
          />
          <input type="file" ref="fileInput" class="hidden" @change="uploadPhoto" />

        <div class="flex flex-col gap-2 flex-1">
          <div class="flex items-center gap-4">
            <div class="font-bold tracking-wider">{{ user.name }}</div>
            <div class="text-white/50">@{{ user.username }}</div>
          </div>

          <div class="text-white/40 text-sm">{{ user.email }}</div>
        </div>
      </div>

      <div v-else class="flex items-center gap-8 animate-pulse">
        <div
          class="w-28 h-28 rounded-full bg-[#1d1d21] border border-white/10 shadow-xl mb-4"
        ></div>

        <div class="flex flex-col gap-3 flex-1">
          <div class="flex items-center gap-4">
            <div class="h-6 w-48 bg-[#1d1d21] rounded"></div>
            <div class="h-6 w-32 bg-[#1d1d21] rounded"></div>
          </div>

          <div class="h-4 w-40 bg-[#1d1d21] rounded"></div>
        </div>
      </div>

      <div class="flex gap-10 border-b border-white/10 pb-4 mt-8">
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
        <div class="h-4 w-16 bg-[#1d1d21] rounded"></div>
      </div>

      <div class="grid grid-cols-3 w-[50%] gap-4 mt-8">
        <div v-for="n in 6" :key="n" class="p-3">
          <div class="w-full bg-[#1d1d21] rounded-lg aspect-[9/16]"></div>
        </div>
      </div>

    </div>

    <div class="w-80 border-l border-white/5 px-6 py-8 overflow-auto space-y-4">
      <div class="h-6 w-40 bg-[#1d1d21] rounded"></div>
      <div v-for="n in 4" :key="n" class="h-20 rounded-xl bg-[#1d1d21]"></div>
    </div>
    <CreateButton @create="handleCreate" />

  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import SideNav from '../components/SideNav.vue'
import CreateButton from '../components/CreateButton.vue'

const user = ref(null)
const fileInput = ref(null)

onMounted(async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('Uživatel není přihlášen')

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    const res = await axios.get('/api/me')
    user.value = res.data
  } catch (err) {
    console.error('Chyba při načítání uživatele:', err)
  }
})

const triggerUpload = () => {
  fileInput.value.click()
}

const uploadPhoto = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('photo', file)

  try {
    const res = await axios.post('/api/profile/photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    user.value.profile_pic_url = res.data.profile_pic_url
  } catch (err) {
    console.error('Chyba při uploadu:', err)
  }
}

const handleCreate = (type) => {
  if (type === 'event') {
    console.log('Otevři modal pro přidání Eventu')
  } else if (type === 'post') {
    console.log('Otevři modal pro přidání Postu')
  } else if (type === 'video') {
    console.log('Otevři modal pro přidání Video')
  }
}
</script>

