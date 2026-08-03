<template>
  <div>
    <div>
      <div class="text-sm text-app-muted mb-3">Vložit body z papíru</div>
      <textarea :value="rawPoints" @input="$emit('update:rawPoints', $event.target.value)" placeholder="Jméno 5, Druhý 4 nebo každé jméno na nový řádek" class="w-full rounded-2xl border border-app bg-app p-3 text-sm h-36 resize-none"></textarea>
      <div class="flex gap-2 mt-3">
        <button @click="$emit('parse')" type="button" class="rounded-2xl bg-pink-500 hover:bg-pink-600 text-white px-3 py-2 text-sm">Načíst body</button>
        <button @click="$emit('apply')" type="button" :disabled="!parsedParticipants.length" class="rounded-2xl bg-surface px-3 py-2 text-sm">Aplikovat do slotů</button>
        <button @click="$emit('update:rawPoints', '')" type="button" class="rounded-2xl bg-transparent border border-app px-3 py-2 text-sm">Vyčistit</button>
        <button @click="$emit('save')" type="button" :disabled="!parsedParticipants.length" class="ml-auto rounded-2xl bg-emerald-500 text-white px-3 py-2 text-sm">Uložit body</button>
      </div>

      <div v-if="parsedParticipants.length" class="mt-3 space-y-2">
        <div v-for="(p, idx) in parsedParticipants" :key="idx" class="flex items-center justify-between gap-3">
          <div class="truncate text-sm">{{ p.name }}</div>
          <input :value="p.points" @input="$emit('update:parsedParticipant', { idx, points: Number($event.target.value) })" class="w-20 text-sm rounded-md bg-surface p-1 text-center" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({ rawPoints: String, parsedParticipants: Array })
const emit = defineEmits(['parse', 'apply', 'save', 'update:rawPoints', 'update:parsedParticipant'])
</script>
