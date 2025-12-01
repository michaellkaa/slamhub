<template>
  <div class="flex flex-col gap-5 px-8 pt-6 pb-8 w-full max-w-sm">
    <input
      type="email"
      v-model="email"
      placeholder="Email"
      class="p-3 px-10"
      required
    />
    <input
      type="password"
      v-model="password"
      placeholder="Heslo"
      class="p-3 px-10"
      required
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
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const email = ref('');
const password = ref('');
const error = ref('');

const login = async () => {
  error.value = '';
  try {
    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    });

    console.log('User logged in:', res.data);

    localStorage.setItem('token', res.data.access_token);

    axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.access_token}`;

    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || 'Chyba přihlášení';
  }
};

// Google login
const loginWithGoogle = () => {
  window.location.href = '/api/auth/google/redirect';
};
</script>
