<template>
  <div class="flex flex-col gap-5 px-8 pt-6 pb-8 w-full max-w-sm">
    <input
      type="email"
      name="email"
      autocomplete="email"
      v-model="email"
      placeholder="Email"
      class="p-3 px-10"
    />

    <input
      type="password"
      name="password"
      autocomplete="current-password"
      v-model="password"
      placeholder="Heslo"
      class="p-3 px-10"
    />

    <button
      @click="login"
      class="bg-[#DF68CF] text-white uppercase font-bold p-3 rounded-md"
    >
      Přihlásit se
    </button>

    <button
      @click="loginWithGoogle"
      class="bg-[#DF68CF] text-white uppercase font-bold p-3 rounded-md"
    >
      Přihlásit se přes Google
    </button>

    <p v-if="error" class="text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')

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
    error.value = err.response?.data?.message || 'Chyba přihlášení'
  }
}


const loginWithGoogle = () => {
  window.location.href = '/api/auth/google/redirect'
}
</script>