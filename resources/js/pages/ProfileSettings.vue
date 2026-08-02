<template>
  <div class="min-h-screen bg-app text-app flex justify-center px-3 md:px-4 py-6 md:py-10 pb-24 lg:pb-10">
    <div class="w-full max-w-4xl">
      <button @click="goBack" class="mb-6 text-sm text-app-muted hover:text-app transition">
        ←
      </button>

      <div class="flex flex-col md:flex-row gap-6 md:gap-10">
        <aside class="md:w-48 shrink-0">
          <nav class="flex flex-col gap-1">
            <button
              v-for="section in sections"
              :key="section.key"
              type="button"
              @click="activeSection = section.key"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition text-left w-full"
              :class="activeSection === section.key
                ? 'bg-surface text-app'
                : 'text-app-muted hover:text-app hover:bg-surface'"
            >
              <span class="w-5 h-5 flex items-center justify-center opacity-80" v-html="section.icon"></span>
              {{ section.label }}
            </button>
          </nav>
        </aside>

        <div class="flex-1 min-w-0 rounded-xl p-1 md:p-2">
          <template v-if="activeSection === 'profile'">
            <h1 class="text-xl font-semibold">Profil</h1>
            <p class="text-app-muted text-sm mt-1">Uprav své jméno a uživatelské jméno.</p>

            <div class="mt-6 grid gap-3">
              <label class="text-xs text-app-muted">Jméno</label>
              <input v-model="form.name" type="text"
                class="h-10 px-3 rounded-md bg-surface text-app border border-app focus:outline-none" />

              <label class="text-xs text-app-muted mt-2">Username</label>
              <input v-model="form.username" type="text"
                class="h-10 px-3 rounded-md bg-surface text-app border border-app focus:outline-none" />
            </div>

            <button @click="saveSettings" :disabled="isSaving"
              class="mt-5 bg-surface-hover hover:bg-surface-active disabled:opacity-60 text-app font-semibold px-4 py-2 rounded-md transition">
              {{ isSaving ? 'Ukládám...' : 'Uložit změny' }}
            </button>
            <p v-if="success" class="mt-3 text-sm text-green-400">{{ success }}</p>
            <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>

            <hr class="my-6 border-app" />

            <button @click="logout" :disabled="isLoggingOut"
              class="bg-pink-500 hover:bg-pink-600 disabled:opacity-60 text-white font-semibold px-4 py-2 rounded-md transition">
              {{ isLoggingOut ? 'Odhlašuji...' : 'Odhlásit se' }}
            </button>
          </template>

          <template v-else-if="activeSection === 'appearance'">
            <h1 class="text-xl font-semibold">Vzhled</h1>
            <p class="text-app-muted text-sm mt-1">Zvol světlý, tmavý režim, nebo podle zařízení.</p>

            <div class="mt-6 grid gap-3">
              <button
                v-for="option in appearanceOptions"
                :key="option.value"
                type="button"
                @click="onSelectTheme(option.value)"
                class="flex items-center gap-4 rounded-xl border px-4 py-3 text-left transition w-full"
                :class="preference === option.value
                  ? 'border-pink-500 bg-surface'
                  : 'border-app bg-transparent hover:bg-surface'"
              >
                <span
                  class="w-10 h-10 rounded-lg border border-app bg-surface text-app flex items-center justify-center shrink-0"
                  v-html="option.icon"
                ></span>
                <span class="min-w-0">
                  <span class="block text-sm font-semibold">{{ option.label }}</span>
                  <span class="block text-xs text-app-muted mt-0.5">{{ option.description }}</span>
                </span>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useTheme } from '../composables/useTheme'

const router = useRouter()
const { preference, setTheme } = useTheme()

const activeSection = ref('profile')

const sections = [
  {
    key: 'profile',
    label: 'Profil',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>`,
  },
  {
    key: 'appearance',
    label: 'Vzhled',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>`,
  },
]

const appearanceOptions = [
  {
    value: 'light',
    label: 'Světlý',
    description: 'Vždy světlý režim',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>`,
  },
  {
    value: 'dark',
    label: 'Tmavý',
    description: 'Vždy tmavý režim',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 0 0 11.5 11.5z"/></svg>`,
  },
  {
    value: 'system',
    label: 'Podle zařízení',
    description: 'Přepíná podle nastavení systému',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>`,
  },
]

const onSelectTheme = (value) => {
  setTheme(value)
}

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
    success.value = 'Profil uložen.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Nepodařilo se uložit profil.'
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
