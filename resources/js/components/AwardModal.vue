<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-[#141418] rounded-xl p-6 w-96 max-h-[80vh] overflow-auto">
      <h2 class="text-lg font-bold mb-4">Vybrat ocenění</h2>

      <div class="flex flex-col gap-2">
        <label v-for="award in awards" :key="award.id" class="flex items-center gap-2">
          <input
            type="radio"
            name="award-selection"
            :value="award.id"
            v-model="selectedAwardLocal"
          />
          {{ award.title }}
        </label>
      </div>

      <div class="flex justify-end mt-4 gap-2">
        <button @click="$emit('update:modelValue', false)" class="px-4 py-2 bg-gray-700 rounded">Zrušit</button>
        <button @click="saveSelection" class="px-4 py-2 bg-pink-500 rounded">Potvrdit</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean,
  awards: Array,
  selectedAwards: Array
})

const emit = defineEmits(['update:modelValue', 'update:selectedAwards'])

const selectedAwardLocal = ref(props.selectedAwards[0] ?? null)

watch(() => props.selectedAwards, (newVal) => {
  selectedAwardLocal.value = newVal[0] ?? null
})

const saveSelection = () => {
  emit('update:selectedAwards', selectedAwardLocal.value ? [selectedAwardLocal.value] : [])
  emit('update:modelValue', false)
}
</script>
