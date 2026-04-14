<template>
  <div
    v-if="visible"
    class="fixed left-1/2 bottom-5 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2"
  >
    <div class="rounded-2xl border border-white/10 bg-[#141418]/95 backdrop-blur px-4 py-3 text-white shadow-xl">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-pink-400"></div>
        <div class="flex-1">
          <div class="text-sm font-semibold">
            {{ offlineReady ? 'Aplikace je připravená offline' : 'Je dostupná nová verze' }}
          </div>
          <div class="mt-0.5 text-xs text-white/70">
            {{ offlineReady ? 'Můžeš pokračovat i bez připojení.' : 'Klikni pro načtení aktualizace.' }}
          </div>
        </div>

        <button
          class="text-white/60 hover:text-white text-sm px-2"
          @click="close"
          aria-label="Zavřít"
          title="Zavřít"
        >
          ✕
        </button>
      </div>

      <div v-if="needRefresh" class="mt-3 flex gap-2">
        <button
          class="flex-1 rounded-xl bg-pink-500 hover:bg-pink-400 transition text-white text-sm font-semibold py-2"
          @click="updateServiceWorker(true)"
        >
          Aktualizovat
        </button>
        <button
          class="rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 transition text-white text-sm font-semibold py-2 px-4"
          @click="close"
        >
          Později
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRegisterSW } from 'virtual:pwa-register/vue'

const closed = ref(false)

const {
  offlineReady,
  needRefresh,
  updateServiceWorker,
} = useRegisterSW()

const visible = computed(() => !closed.value && (offlineReady.value || needRefresh.value))

watch([offlineReady, needRefresh], () => {
  if (offlineReady.value || needRefresh.value) closed.value = false
})

const close = () => {
  closed.value = true
}
</script>

