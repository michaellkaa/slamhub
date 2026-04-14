<template>
  <button
    type="button"
    class="rounded-xl border border-[#BF2679] h-20 lg:h-24 px-4 lg:px-6 flex items-center w-full bg-[#141418] hover:bg-[#18181d] transition shadow-[0_0_0_1px_rgba(191,38,121,0.2)]"
    @click="$emit('select', row)"
  >
    <div class="w-full grid grid-cols-[48px_56px_1fr_auto] items-center gap-3 text-[#FFF7CC]">
      <h1 class="text-lg lg:text-xl font-bold text-center">{{ row.rank }}</h1>
      <img :src="row.profile_pic_url || '/images/default-avatar.png'" alt="" class="h-12 w-12 lg:h-14 lg:w-14 rounded-full object-cover" />
      <div class="min-w-0">
        <h1 class="text-base lg:text-xl truncate font-semibold">{{ row.name }}</h1>
        <p class="text-xs lg:text-sm text-white/60 truncate">@{{ row.username }}</p>
      </div>
      <div class="text-right">
        <h1 class="text-lg lg:text-xl font-bold leading-tight">{{ row.awards_count }}</h1>
        <p class="text-[11px] text-white/55 leading-tight">awards</p>
      </div>
    </div>
  </button>
</template>

<script setup>
defineProps({
  row: {
    type: Object,
    required: true,
  },
})

defineEmits(['select'])
</script>