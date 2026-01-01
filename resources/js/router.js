import { createRouter, createWebHistory } from "vue-router";

import AwardPages from '../js/pages/AwardPages.vue'
import HomePage from "./pages/HomePage.vue";
import LoginPage from "./pages/LoginPage.vue";
import RegisterPage from './pages/RegisterPage.vue';
import ProfilePage from "./pages/ProfilePage.vue";
import EventPage from "./pages/EventPage.vue";
import DirectMessages from "./pages/DirectMessages.vue";
import CreateEvent from "./pages/CreateEvent.vue";
import EventDetail from './pages/EventDetail.vue';
import CreatePost from "./pages/CreatePost.vue";


const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
    { path: "/login", name: "login", component: LoginPage },
    { path: '/register', component: RegisterPage },
    { path: '/profile', component: ProfilePage },
    { path: '/events', component: EventPage },
    { path: '/messages', component: DirectMessages },
    {path: '/events/create', name: 'CreateEvent', component: CreateEvent },
    { path: '/events/:id', name: 'EventDetail', component: EventDetail, props: true },
    { path: '/posts/create', name: 'CreatePost', component: CreatePost }
];

export default createRouter({
  history: createWebHistory(),
  routes,
});



