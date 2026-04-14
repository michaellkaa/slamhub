<template>
  <div class="min-h-screen bg-[#0f0f12] text-white flex items-center justify-center px-6 py-10">
    <div class="w-full max-w-sm">
      <div class="mb-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/70">
          SlamHub
        </div>
        <h1 class="mt-4 text-3xl font-black tracking-tight">
          Registrace
        </h1>
        <p class="mt-2 text-sm text-white/70">
          Vytvoř si profil a začni sledovat komunitu.
        </p>
      </div>

      <div class="rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-6">
        <RegisterForm />

        <p class="mt-6 text-sm text-white/70">
          Už máš účet?
          <router-link to="/login" class="text-pink-300 hover:text-pink-200 font-semibold">Přihlas se</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import RegisterForm from '../components/RegisterForm.vue';
</script>
