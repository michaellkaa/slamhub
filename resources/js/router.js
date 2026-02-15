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
import CreatePost from "./Pages/CreatePost.vue";
import CreateAward from "./pages/CreateAward.vue";
import ProfileDetail from "./Pages/ProfileDetail.vue";

const routes = [
    
    { path: "/", name: "home", component: HomePage },
    { path: "/awards", name: "awards", component: AwardPages },
    { path: "/login", name: "login", component: LoginPage },
    { path: "/register", name: "register", component: RegisterPage },
    {
      path: "/profile",
      redirect: () => {
        try {
          const user = JSON.parse(localStorage.getItem('user'))
          if (user?.username) {
            return `/profile/${user.username}`
          } else {
            return '/login'
          }
        } catch {
          return '/login'
        }
      }
    },
    { path: "/profile/:username", name: "profile.detail", component: ProfileDetail, props: true },
    { path: "/events", name: "events", component: EventPage },
    { path: "/messages", name: "messages", component: DirectMessages },
    { path: "/events/create", name: "CreateEvent", component: CreateEvent },
    { path: "/events/:id", name: "EventDetail", component: EventDetail, props: true },
    { path: "/posts/create", name: "CreatePost", component: CreatePost },
    { path: "/awards/create", name: "CreateAward", component: CreateAward },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
