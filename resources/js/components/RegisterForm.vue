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

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');

const register = async () => {
  error.value = '';
  try {
    const response = await axios.post('/api/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });
    console.log('Registered user:', response.data);
    // po registraci můžeš přesměrovat na login nebo rovnou přihlásit
  } catch (err) {
    if (err.response && err.response.data) {
      error.value = err.response.data.message || 'Chyba při registraci';
    } else {
      error.value = 'Chyba při registraci';
    }
  }
};
</script>
