<template>
  <div class="min-h-screen bg-[#0f0f12] text-white flex justify-center px-3 md:px-4 py-6 md:py-10 pb-24 lg:pb-10">
    <div class="w-full max-w-xl">
      <button
        @click="goBack"
        class="mb-6 text-sm text-white/70 hover:text-white transition"
      >
        ← Back to profile
      </button>

      <div class="rounded-xl border border-white/10 bg-[#141418] p-4 md:p-6">
        <h1 class="text-xl font-semibold">Settings</h1>
        <p class="text-white/60 text-sm mt-1">Update your profile details.</p>

        <div class="mt-6 grid gap-3">
          <label class="text-xs text-white/60">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="h-10 px-3 rounded-md bg-[#1d1d21] text-white border border-white/10 focus:outline-none"
          />

          <label class="text-xs text-white/60 mt-2">Username</label>
          <input
            v-model="form.username"
            type="text"
            class="h-10 px-3 rounded-md bg-[#1d1d21] text-white border border-white/10 focus:outline-none"
          />
        </div>

        <button
          @click="saveSettings"
          :disabled="isSaving"
          class="mt-5 bg-[#2a2a30] hover:bg-[#3a3a3a] disabled:opacity-60 text-white font-semibold px-4 py-2 rounded-md transition"
        >
          {{ isSaving ? 'Saving...' : 'Save changes' }}
        </button>
        <p v-if="success" class="mt-3 text-sm text-green-400">{{ success }}</p>
        <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>

        <hr class="my-6 border-white/10" />

        <button
          @click="logout"
          :disabled="isLoggingOut"
          class="bg-[#DF68CF] hover:bg-[#c857b8] disabled:opacity-60 text-white font-semibold px-4 py-2 rounded-md transition"
        >
          {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({
  name: '',
  username: '',
})

const isSaving = ref(false)
const success = ref('')
const error = ref('')
const isLoggingOut = ref(false)

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  axios.defaults.headers.common.Authorization = `Bearer ${token}`

  try {
    const { data } = await axios.get('/api/me')
    form.value.name = data.name || ''
    form.value.username = data.username || ''
  } catch (err) {
    console.error('Failed to load current user:', err)
    router.push('/login')
  }
})

const saveSettings = async () => {
  success.value = ''
  error.value = ''
  isSaving.value = true

  try {
    const { data } = await axios.put('/api/me', {
      name: form.value.name,
      username: form.value.username,
    })
    localStorage.setItem('user', JSON.stringify(data))
    success.value = 'Profile updated.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update profile.'
  } finally {
    isSaving.value = false
  }
}

const logout = async () => {
  isLoggingOut.value = true
  try {
    await axios.post('/api/logout')
  } catch (err) {
    console.error('Logout API failed:', err)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    delete axios.defaults.headers.common.Authorization
    isLoggingOut.value = false
    router.push('/login')
  }
}

const goBack = () => {
  const cached = JSON.parse(localStorage.getItem('user') || 'null')
  if (cached?.username) {
    router.push(`/profile/${cached.username}`)
    return
  }
  router.push('/profile')
}
</script>
