import { createRouter, createWebHistory } from "vue-router";

import AwardPages from '../js/pages/AwardPages.vue'
import HomePage from "./pages/HomePage.vue";
import LoginPage from "./pages/LoginPage.vue";

const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
    { path: "/login", name: "login", component: LoginPage },

];

export default createRouter({
  history: createWebHistory(),
  routes,
});