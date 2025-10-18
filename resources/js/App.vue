<template>
    <div class="bg-[#1C1C1C] w-screen h-screen flex overflow-hidden">
        <div class="h-full z-10 shadow-[4px_0_15px_rgba(0,0,0,0.4)]">
            <SideNav />
        </div>

        <!--<div class="flex-row flex justify-center items-start mt-28 overflow-y-auto">
            <div class="flex flex-col">
                <SwitchTitle text="Liga" :isActive="activeTab === 'league'" @click="activeTab = 'league'" />
                <League />

            </div>
            <div class="flex flex-col">
                <SwitchTitle text="Žebříček" :isActive="activeTab === 'leaderboard'"
                    @click="activeTab = 'leaderboard'" />
                <Leaderboard />
            </div>
        </div>-->
        <div class="grid grid-cols-2 mt-28 overflow-y-auto justify-center w-screen">
            <div class="flex flex-col">
                <SwitchTitle text="Liga" :isActive="activeTab === 'league'" @click="activeTab = 'league'" />
                <League />

            </div>
            <div class="flex flex-col">
                <SwitchTitle text="Žebříček" :isActive="activeTab === 'leaderboard'"
                    @click="activeTab = 'leaderboard'" />
                <Leaderboard />
            </div>
        </div>
    </div>
</template>

<script setup>
import SideNav from './components/SideNav.vue'
import Leaderboard from './components/Leaderboard.vue'
import League from './components/League.vue';
import SwitchTitle from './components/SwitchTitle.vue';
import { ref } from 'vue';

const activeTab = ref('leaderboard');
</script>