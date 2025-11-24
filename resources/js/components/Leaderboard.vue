<template>
    <div class=" max-h-screen flex justify-center overflow-hidden mt-10 w-full">
        <!--nejaky systen jakoze for each, nemazat tento soubor :D -->
      <div class="flex flex-col items-center gap-10 lg:gap-4">
        <LeaderboardRow />
        <LeaderboardRow />
      </div>
    </div>
  </template>
  
  <script setup>
    import LeaderboardRow from './LeaderboardRow.vue';

  </script>
  