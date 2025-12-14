<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
    @click="close"
  >
    <div
      class="bg-[#141418] rounded-xl p-6 w-full max-w-md shadow-xl"
      @click.stop
    >
      <h2 class="text-lg font-bold mb-4">Vybrat performery</h2>

      <!-- EXISTUJÍCÍ PERFORMEŘI -->
      <div class="max-h-40 overflow-y-auto space-y-2 mb-4">
        <label
          v-for="p in performers"
          :key="p.id"
          class="flex items-center gap-2 text-sm"
        >
          <input
            type="checkbox"
            :value="p.id"
            v-model="localPerformers"
          />
          {{ p.name }}
        </label>
      </div>

      <!-- EXTERNÍ PERFORMEŘI -->
      <div class="mb-4">
        <label class="text-sm text-white/80">
          Externí performer (bez profilu)
        </label>

        <div class="flex gap-2 mt-1">
          <input
            v-model="guestInput"
            placeholder="např. DJ Ghost"
            class="flex-1 p-2 rounded bg-[#1d1d21] text-white"
          />
          <button
            type="button"
            @click="addGuest"
            class="px-3 rounded bg-pink-500 text-sm font-semibold"
          >
            Přidat
          </button>
        </div>

        <div class="flex flex-wrap gap-2 mt-2">
          <span
            v-for="(g, i) in localGuests"
            :key="i"
            class="bg-pink-500/20 px-2 py-1 rounded text-xs flex items-center gap-1"
          >
            {{ g }}
            <button
              type="button"
              @click="removeGuest(i)"
              class="text-white/60 hover:text-white"
            >
              ×
            </button>
          </span>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="close"
          class="text-white/60 hover:text-white"
        >
          Zrušit
        </button>

        <button
          type="button"
          @click="confirm"
          class="bg-pink-500 px-4 py-2 rounded font-bold hover:bg-pink-600"
        >
          Potvrdit
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  performers: {
    type: Array,
    default: () => []
  },
  selectedPerformers: {
    type: Array,
    default: () => []
  },
  guestPerformers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:performers',
  'update:guests'
])

const localPerformers = ref([])
const localGuests = ref([])
const guestInput = ref('')

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      localPerformers.value = [...props.selectedPerformers]
      localGuests.value = [...props.guestPerformers]
    }
  }
)

const addGuest = () => {
  if (!guestInput.value.trim()) return

  if (localGuests.value.includes(guestInput.value.trim())) return

  localGuests.value.push(guestInput.value.trim())
  guestInput.value = ''
}

const removeGuest = (i) => {
  localGuests.value.splice(i, 1)
}

const close = () => {
  emit('update:modelValue', false)
}

const confirm = () => {
  emit('update:performers', localPerformers.value)
  emit('update:guests', localGuests.value)
  emit('update:modelValue', false)
}
</script>
