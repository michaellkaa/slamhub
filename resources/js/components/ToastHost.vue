<template>
  <div class="pointer-events-none fixed top-4 right-4 z-[200] flex w-[min(22rem,calc(100vw-2rem))] flex-col gap-2">
    <TransitionGroup name="toast">
      <div
        v-for="item in toasts"
        :key="item.id"
        class="pointer-events-auto rounded-xl border px-4 py-3 text-sm shadow-xl backdrop-blur"
        :class="toastClass(item.type)"
        role="status"
        aria-live="polite"
      >
        <div class="flex items-start gap-3">
          <p class="flex-1 leading-snug break-words">{{ item.message }}</p>
          <button
            type="button"
            class="shrink-0 text-xs opacity-70 transition hover:opacity-100"
            aria-label="Zavřít"
            @click="dismiss(item.id)"
          >
            ✕
          </button>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast'

const { toasts, dismiss } = useToast()

const toastClass = (type) => {
  if (type === 'success') return 'border-emerald-500/40 bg-surface text-app shadow-[0_0_0_1px_rgba(16,185,129,0.15)]'
  if (type === 'error') return 'border-red-500/40 bg-surface text-app shadow-[0_0_0_1px_rgba(239,68,68,0.15)]'
  return 'border-app bg-surface text-app'
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.22s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(12px);
}
.toast-move {
  transition: transform 0.22s ease;
}
</style>
