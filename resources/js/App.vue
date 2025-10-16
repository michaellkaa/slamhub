<template>
    <div class="bg-[#1C1C1C] w-screen h-screen flex flex-row">
        <div class="flex flex-col h-screen">
        <SideNav/>

        </div>
        <div class="">
        <h2 class="text-red-700 bg-blue-400">
        VueSchool Rules!
        </h2>
        <Test/>
        </div>

    </div>
</template>

<script setup>
import Test from './components/Test.vue'
import SideNav from './components/SideNav.vue';
</script>