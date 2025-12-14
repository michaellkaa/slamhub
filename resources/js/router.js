import { createRouter, createWebHistory } from "vue-router";

import AwardPages from '../js/pages/AwardPages.vue'
import HomePage from "./pages/HomePage.vue";
import LoginPage from "./pages/LoginPage.vue";
import RegisterPage from './pages/RegisterPage.vue';
import ProfilePage from "./pages/ProfilePage.vue";
import EventPage from "./pages/EventPage.vue";
import DirectMessages from "./pages/DirectMessages.vue";
import CreateEvent from "./pages/CreateEvent.vue";

const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
    { path: "/login", name: "login", component: LoginPage },
    { path: '/register', component: RegisterPage },
    { path: '/profile', component: ProfilePage },
    { path: '/events', component: EventPage },
    { path: '/messages', component: DirectMessages },
    {path: '/events/create', name: 'CreateEvent', component: CreateEvent },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});


