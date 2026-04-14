<template>
  <div class="min-h-screen bg-[#0f0f12] text-white flex">
    <div class="flex-1 flex items-center justify-center px-6 py-10">
      <div class="w-full max-w-sm">
        <div class="mb-8">
          <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/70">
            SlamHub
          </div>
          <h1 class="mt-4 text-3xl font-black tracking-tight">
            Přihlášení
          </h1>
          <p class="mt-2 text-sm text-white/70">
            Pokračuj do aplikace a sleduj akce, videa i žebříčky.
          </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-6">
          <LoginForm />

          <p class="mt-6 text-sm text-white/70">
            Nemáš účet?
            <router-link to="/register" class="text-pink-300 hover:text-pink-200 font-semibold">Zaregistruj se</router-link>
          </p>
        </div>
      </div>
    </div>

    <!--<div class="hidden lg:block w-[42%] relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-b from-pink-500/20 via-transparent to-transparent"></div>
      <img src="../../../public/boleslam-23.jpg" alt="" class="h-full w-full object-cover opacity-70" />
    </div>-->
  </div>
</template>

<script setup>
import LoginForm from '../components/LoginForm.vue';
</script>
