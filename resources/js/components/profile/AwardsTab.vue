<template>
  <div class="mt-8 w-[50%] space-y-4">

    <div
      v-for="award in awards"
      :key="award.id"
      class="flex items-center gap-4 p-4 bg-[#1d1d21] rounded-xl"
    >
      <img
        :src="award.icon_url"
        class="w-10 h-10 object-contain"
      />

      <div>
        <div class="font-medium">
          {{ award.name }}
        </div>

        <div class="text-sm text-white/40">
          {{ award.year }}
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const awards = ref([])

onMounted(async () => {
  const res = await axios.get('/api/profile/awards')
  awards.value = res.data
})
</script>
