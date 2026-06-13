<template>
  <div class="flex flex-col gap-4 w-full">
    <label class="text-xs text-white/60">Email</label>
    <input
      type="email"
      name="email"
      autocomplete="email"
      v-model="email"
      placeholder="např. novak@email.cz"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
    />

    <label class="mt-2 text-xs text-white/60">Heslo</label>
    <input
      type="password"
      name="password"
      autocomplete="current-password"
      v-model="password"
      placeholder="••••••••"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
    />

    <button
      @click="login"
      class="mt-4 w-full rounded-2xl bg-pink-500 hover:bg-pink-400 transition text-white font-semibold py-3"
    >
      Přihlásit se
    </button>

    <div class="flex items-center gap-3 my-2">
      <div class="h-px flex-1 bg-white/10"></div>
      <div class="text-xs text-white/50">nebo</div>
      <div class="h-px flex-1 bg-white/10"></div>
    </div>

    <button
      @click="loginWithGoogle"
      class="w-full rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition text-white font-semibold py-3 flex items-center justify-center gap-2"
    >
      <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
        <path fill="#FFC107"
          d="M43.611 20.083H42V20H24v8h11.303C33.651 32.656 29.155 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" />
        <path fill="#FF3D00"
          d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
        <path fill="#4CAF50"
          d="M24 44c5.052 0 9.735-1.934 13.243-5.078l-6.115-5.174C29.084 35.186 26.684 36 24 36c-5.134 0-9.618-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
        <path fill="#1976D2"
          d="M43.611 20.083H42V20H24v8h11.303c-.792 2.223-2.231 4.114-4.175 5.247l.002-.001 6.115 5.174C36.813 39.02 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" />
      </svg>
      Přihlásit se přes Google
    </button>

    <!-- ERROR / INFO STATE -->
    <div v-if="error" class="mt-3 space-y-2">

      <!-- INFO: neověřený email -->
      <div
        v-if="isUnverified"
        class="rounded-2xl border border-white/10 bg-white/5 p-3"
      >
        <p class="text-sm text-white/60">
          {{ error }}
        </p>

        <button
          @click="goToVerify"
          class="mt-2 w-full rounded-xl bg-white/10 hover:bg-white/20 transition text-white text-sm py-2"
        >
          Ověřit email
        </button>
      </div>

      <!-- ERROR -->
      <p v-else class="text-sm text-red-300">
        {{ error }}
      </p>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')

const isUnverified = computed(() =>
  error.value?.includes('Email není ověřen')
)

const login = async () => {
  error.value = ''

  try {
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    })

    const token = response.data?.access_token
    const loggedInUser = response.data?.user

    if (!token || !loggedInUser) {
      throw new Error('Missing token or user in login response')
    }

    localStorage.setItem('token', token)
    localStorage.setItem('user', JSON.stringify(loggedInUser))
    axios.defaults.headers.common.Authorization = `Bearer ${token}`

    const user = await axios.get('/api/me')

    router.push(`/profile/${user.data.username}`)

  } catch (err) {
    const data = err.response?.data

    if (data?.action === 'verify_email') {
      sessionStorage.setItem('verification_email', email.value)

      router.push({ name: 'verify-email' })
      return
    }

    error.value = data?.message || 'Chyba přihlášení'
  }
}

const goToVerify = () => {
  sessionStorage.setItem('verification_email', email.value)
  router.push({ name: 'verify-email' })
}

const loginWithGoogle = () => {
  window.location.href = '/api/auth/google/redirect'
}
</script>