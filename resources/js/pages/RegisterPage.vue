<template>
  <div class="flex flex-col justify-center items-center w-screen h-screen bg-[#E1FF00]">
    <h1 class="uppercase font-black text-[#DF68CF] text-[54px]">
      Registrace
    </h1>
    <RegisterForm />
    <p class="mt-3">
      Už máš účet?
      <router-link to="/login" class="text-[#DF68CF] font-bold">Přihlas se</router-link>
    </p>
  </div>
</template>

<script setup>
import RegisterForm from '../components/RegisterForm.vue';
</script>
