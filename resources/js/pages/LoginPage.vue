<template>
  <div class="flex flex-row max-w-screen max-h-screen overflow-hidden">
    <div class="bg-[#E1FF00] flex flex-col justify-center items-center w-screen h-screen">
      <div class="flex flex-col justify-center items-center">
        <h1 class="uppercase font-black text-[#DF68CF] text-[54px]">
          Přihlas se
        </h1>
        <LoginForm />
        <p class="my-2">nebo <a href="">se registruj</a></p>
      </div>
    </div>
    <div class="h-auto w-full z-10 shadow-[0_0_15px_6px_rgba(0,0,0,0.4)]">
      <img src="../../../public/boleslam-23.jpg" alt="">
    </div>
  </div>

  <img src="../../../public/Vector 1.png" alt="" class="absolute bottom-5 left-0" />
</template>

<script setup>
import LoginForm from '../components/LoginForm.vue';
import RegisterForm from '../components/RegisterForm.vue';
</script>
