<template>
  <nav class="flex flex-col gap-1">
    <div class="px-1 mb-2">
      <label class="block text-xs text-app-muted mb-1.5">Event</label>
      <input
        :value="searchQuery"
        @input="$emit('update:searchQuery', $event.target.value)"
        @focus="pickerOpen = true"
        type="search"
        placeholder="Hledat event…"
        class="w-full h-9 px-3 rounded-md bg-surface text-app border border-app text-sm focus:outline-none focus:border-pink-500 transition"
      />

      <div v-if="pickerOpen || searchQuery" class="mt-2 max-h-48 overflow-y-auto space-y-0.5">
        <button
          v-for="event in filteredEvents"
          :key="event.id"
          type="button"
          @click="onSelect(event.id)"
          class="w-full text-left px-3 py-2 rounded-lg text-sm transition"
          :class="selectedEventId === event.id
            ? 'bg-surface text-app font-medium'
            : 'text-app-muted hover:text-app hover:bg-surface'"
        >
          <span class="block truncate">{{ event.title || 'Bez názvu' }}</span>
          <span class="block text-xs text-app-muted mt-0.5">
            {{ event.event_mode === 'league' ? 'Liga' : 'Exhibice' }} · {{ formatDate(event.starts_at) }}
          </span>
        </button>

        <p v-if="!filteredEvents.length" class="px-3 py-2 text-xs text-app-muted">
          {{ searchQuery ? 'Nic nenalezeno.' : 'Žádné eventy.' }}
        </p>

        <div v-if="!searchQuery && events.length > filteredEvents.length" class="px-3 pt-1">
          <button
            type="button"
            @click="$emit('update:showAllEvents', true)"
            class="text-xs text-pink-500 hover:underline"
          >
            Zobrazit všechny
          </button>
        </div>
        <div v-else-if="!searchQuery && showAllEvents && events.length > 4" class="px-3 pt-1">
          <button
            type="button"
            @click="$emit('update:showAllEvents', false)"
            class="text-xs text-app-muted hover:underline"
          >
            Skrýt seznam
          </button>
        </div>
      </div>

      <div
        v-else-if="selectedEvent"
        class="mt-2 px-3 py-2 rounded-lg bg-surface text-sm"
      >
        <span class="block font-medium truncate">{{ selectedEvent.title || 'Bez názvu' }}</span>
        <span class="block text-xs text-app-muted mt-0.5">
          {{ selectedEvent.event_mode === 'league' ? 'Liga' : 'Exhibice' }}
        </span>
        <button
          type="button"
          @click="pickerOpen = true"
          class="mt-1 text-xs text-pink-500 hover:underline"
        >
          Změnit event
        </button>
      </div>
    </div>

    <div class="border-t border-app my-2"></div>

    <button
      v-for="tab in tabs"
      :key="tab.key"
      type="button"
      @click="$emit('update:activeTab', tab.key)"
      class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition text-left w-full"
      :class="activeTab === tab.key
        ? 'bg-surface text-app'
        : 'text-app-muted hover:text-app hover:bg-surface'"
    >
      <span class="w-5 h-5 flex items-center justify-center opacity-80" v-html="tab.icon"></span>
      {{ tab.label }}
    </button>
  </nav>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  searchQuery: String,
  events: Array,
  filteredEvents: Array,
  selectedEventId: [String, Number],
  showAllEvents: Boolean,
  activeTab: String,
  formatDate: Function,
})

const emit = defineEmits(['select', 'update:searchQuery', 'update:showAllEvents', 'update:activeTab'])

const pickerOpen = ref(!props.selectedEventId)

const selectedEvent = computed(
  () => props.events?.find((e) => e.id === props.selectedEventId) || null,
)

const tabs = [
  {
    key: 'league',
    label: 'Liga',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 20l9-5-9-5-9 5 9 5z"/><path d="M12 12V4"/></svg>`,
  },
  {
    key: 'manual',
    label: 'Manuální bodování',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>`,
  },
  {
    key: 'voting',
    label: 'Hlasování',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
  },
]

const onSelect = (id) => {
  pickerOpen.value = false
  emit('select', id)
  emit('update:searchQuery', '')
}

watch(
  () => props.selectedEventId,
  (id) => {
    if (id && !props.searchQuery) pickerOpen.value = false
  },
)
</script>
