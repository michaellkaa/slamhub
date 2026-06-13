<template>
  <div class="min-h-screen bg-[#0f0f12] text-white flex items-center justify-center px-6 py-10">
    <div class="w-full max-w-sm">
      <div class="mb-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/70">
          SlamHub
        </div>
        <h1 class="mt-4 text-3xl font-black tracking-tight">
          Ověření e-mailu
        </h1>
        <p class="mt-2 text-sm text-white/70">
          Zadej kód, který jsme ti poslali na e-mail.
        </p>
      </div>

      <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-6">
        <div class="flex flex-col gap-4 w-full">
          <label class="text-xs text-white/60">Email</label>
          <input type="email" v-model="email" placeholder="např. novak@email.cz"
            class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
            required />

          <label class="mt-2 text-xs text-white/60">Ověřovací kód</label>
          <input type="text" v-model="code" placeholder="123456"
            class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
            required />

          <button @click="verify"
            class="mt-4 w-full rounded-2xl bg-pink-500 hover:bg-pink-400 transition text-white font-semibold py-3">
            Ověřit email
          </button>

          <button @click="resendCode"
            class="w-full rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition text-white font-semibold py-3">
            Poslat znovu kód
          </button>

          <p v-if="message" class="text-sm text-green-300">{{ message }}</p>
          <p v-if="error" class="text-sm text-red-300">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const email = ref('')
const code = ref('')
const message = ref('')
const error = ref('')

const verify = async () => {
  error.value = ''
  message.value = ''

  try {
    await axios.post('/api/verify-email', {
      email: email.value,
      code: code.value,
    })

    sessionStorage.removeItem('verification_email')

    message.value = 'Email byl úspěšně ověřen. Můžeš se nyní přihlásit.'

    setTimeout(() => {
      router.push({ name: 'login' })
    }, 1000)

  } catch (err) {
    error.value = err.response?.data?.message || 'Chyba při ověřování e-mailu'
  }
}

const resendCode = async () => {
  error.value = ''
  message.value = ''

  try {
    const response = await axios.post('/api/resend-verification-code', {
      email: email.value,
    })

    message.value = 'Kód byl odeslán. Zkontroluj prosím i složku Spam / Newsletter.'  } catch (err) {
    error.value = err.response?.data?.message || 'Chyba při odesílání kódu'
  }
}

onMounted(() => {
  email.value = sessionStorage.getItem('verification_email') || ''

  if (!email.value) {
    error.value = 'Nebyl nalezen email pro ověření. Zaregistruj se znovu.'
  }
})
</script>
