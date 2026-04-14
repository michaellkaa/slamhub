<template>
  <div class="min-h-screen flex items-center justify-center bg-[#0f0f12] text-white px-6">
    <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-6">
      <div class="text-lg font-semibold">Přihlašuju přes Google…</div>
      <div class="mt-2 text-sm text-white/70">
        Chvilku strpení, dokončujeme přihlášení.
      </div>

      <div v-if="error" class="mt-4 text-sm text-red-300">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const error = ref('')

onMounted(async () => {
  const token = route.query.access_token
  const providerError = route.query.error

  if (providerError && typeof providerError === 'string') {
    error.value = providerError
    return
  }

  if (!token || typeof token !== 'string') {
    error.value = 'Chybí token z Google přihlášení.'
    return
  }

  try {
    localStorage.setItem('token', token)
    axios.defaults.headers.common.Authorization = `Bearer ${token}`

    const { data: me } = await axios.get('/api/me')
    localStorage.setItem('user', JSON.stringify(me))

    router.replace(`/profile/${me.username}`)
  } catch (e) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    error.value = e?.response?.data?.message || 'Nepodařilo se dokončit přihlášení.'
  }
})
</script>

