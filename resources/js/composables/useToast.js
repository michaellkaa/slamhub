import { ref } from 'vue'

const toasts = ref([])
let seed = 0

function dismiss(id) {
  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

function pushToast({ message, type = 'info', duration = 3200 } = {}) {
  const text = String(message || '').trim()
  if (!text) return null

  const id = ++seed
  const toast = {
    id,
    message: text,
    type: ['success', 'error', 'info'].includes(type) ? type : 'info',
  }

  toasts.value = [...toasts.value, toast].slice(-4)

  if (duration > 0) {
    window.setTimeout(() => dismiss(id), duration)
  }

  return id
}

export function useToast() {
  return {
    toasts,
    dismiss,
    toast: pushToast,
    success: (message, duration) => pushToast({ message, type: 'success', duration }),
    error: (message, duration) => pushToast({ message, type: 'error', duration }),
    info: (message, duration) => pushToast({ message, type: 'info', duration }),
  }
}
