<template>
  <div class="flex flex-col gap-4 w-full">
    <label class="text-xs text-white/60">Jméno</label>
    <input
      type="text"
      v-model="name"
      placeholder="např. Adam Novák"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
      required
    />

    <label class="mt-2 text-xs text-white/60">Uživatelské jméno</label>
    <input
      type="text"
      v-model="username"
      placeholder="např. adam_novak"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
      required
    />

    <label class="mt-2 text-xs text-white/60">Email</label>
    <input
      type="email"
      v-model="email"
      placeholder="např. novak@email.cz"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
      required
    />

    <label class="mt-2 text-xs text-white/60">Heslo</label>
    <input
      type="password"
      v-model="password"
      placeholder="••••••••"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
      required
    />

    <label class="mt-2 text-xs text-white/60">Potvrzení hesla</label>
    <input
      type="password"
      v-model="password_confirmation"
      placeholder="••••••••"
      class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm outline-none focus:border-pink-400/50 focus:ring-2 focus:ring-pink-400/20"
      required
    />

    <button
      @click="register"
      class="mt-4 w-full rounded-2xl bg-pink-500 hover:bg-pink-400 transition text-white font-semibold py-3"
    >
      Registrovat se
    </button>

    <p v-if="error" class="text-sm text-red-300">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const username = ref('');
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');

const register = async () => {
  error.value = '';

  try {
    const response = await axios.post('/api/register', {
      username: username.value,
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });

    console.log('Registered user:', response.data);
    router.push('/');
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data.errors;
      error.value = Object.values(errors)[0][0];
    } else {
      error.value = 'Chyba při registraci';
    }
  }
};

</script>
