import { createRouter, createWebHistory } from "vue-router";

import AwardPages from '../js/pages/AwardPages.vue'
import HomePage from "./pages/HomePage.vue";

const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});