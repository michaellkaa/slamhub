<template>
  <div class="flex flex-col gap-5 px-8 pt-6 pb-8 w-full max-w-sm">

    <input
      type="text"
      v-model="name"
      placeholder="Jméno"
      class="p-3 px-10"
      required
    />

    <input
      type="text"
      v-model="username"
      placeholder="Uživatelské jméno"
      class="p-3 px-10"
      required
    />

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

    <input
      type="password"
      v-model="password_confirmation"
      placeholder="Potvrzení hesla"
      class="p-3 px-10"
      required
    />

    <button
      @click="register"
      class="bg-[#DF68CF] text-white uppercase font-bold p-3 rounded-md"
    >
      Registrovat se
    </button>

    <p v-if="error" class="text-red-500">{{ error }}</p>
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
