import { createRouter, createWebHistory } from "vue-router";

import AwardPages from '../js/pages/AwardPages.vue'
import HomePage from "./pages/HomePage.vue";
import LoginPage from "./pages/LoginPage.vue";
import RegisterPage from './pages/RegisterPage.vue';
import ProfilePage from "./pages/ProfilePage.vue";


const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
    { path: "/login", name: "login", component: LoginPage },
    { path: '/register', component: RegisterPage },
    { path: '/profile', component: ProfilePage },

];

export default createRouter({
  history: createWebHistory(),
  routes,
});


