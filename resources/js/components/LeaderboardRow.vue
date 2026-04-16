<template>
  <div
    v-if="loading"
    class="w-full rounded-2xl px-5 py-4 flex items-center gap-4
           bg-[#18181d]/60 border border-white/10
           animate-pulse"
  >
    <div class="w-10 h-5 bg-white/10 rounded"></div>
    <div class="h-12 w-12 rounded-full bg-white/10"></div>

    <div class="flex-1 space-y-2">
      <div class="h-4 w-1/3 bg-white/10 rounded"></div>
      <div class="h-3 w-1/4 bg-white/10 rounded"></div>
    </div>

    <div class="h-5 w-10 bg-white/10 rounded"></div>
  </div>

  <button
    v-else
    type="button"
    class="w-full rounded-2xl px-5 py-4 flex items-center gap-4
           bg-[#18181d]/80 backdrop-blur-md
           border border-white/10
           hover:border-[#BF2679]/40 hover:bg-[#1c1c22]
           transition-all duration-200
           shadow-md hover:shadow-lg"
    @click="$emit('select', row)"
  >
    <div class="w-10 text-center text-lg font-bold" :class="rankColor">
      {{ row.rank }}
    </div>

    <img
      :src="row.profile_pic_url || '/images/default-avatar.png'"
      class="h-12 w-12 rounded-full object-cover ring-2 ring-white/10"
    />

    <div class="flex-1 min-w-0">
      <h1 class="text-base lg:text-lg font-semibold truncate text-white">
        {{ row.name }}
      </h1>
      <p class="text-xs text-white/50 truncate">
        @{{ row.username }}
      </p>
    </div>

    <div class="text-right">
      <h1 class="text-lg font-bold text-[#BF2679]">
        {{ row.points ?? 0 }}
      </h1>
    </div>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  row: Object,
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['select'])

const rankColor = computed(() => {
  if (!props.row) return 'text-white/30'
  if (props.row.rank === 1) return 'text-yellow-400'
  if (props.row.rank === 2) return 'text-gray-300'
  if (props.row.rank === 3) return 'text-orange-400'
  return 'text-white/70'
})
</script>
