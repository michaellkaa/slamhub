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
import ProfileDetail from "./pages/ProfileDetail.vue";
import UploadVideo from "./pages/UploadVideo.vue";
import ProfileSettings from "./pages/ProfileSettings.vue";

const routes = [
    
    { path: "/", name: "home", component: HomePage, meta: { requiresAuth: true } },
    { path: "/awards", name: "awards", component: AwardPages, meta: { requiresAuth: true } },
    { path: "/login", name: "login", component: LoginPage, meta: { guestOnly: true } },
    { path: "/register", name: "register", component: RegisterPage, meta: { guestOnly: true } },
    {
      path: "/profile",
      meta: { requiresAuth: true },
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
    { path: "/profile/:username", name: "profile.detail", component: ProfileDetail, props: true, meta: { requiresAuth: true } },
    { path: "/settings", name: "settings", component: ProfileSettings, meta: { requiresAuth: true } },
    { path: "/events", name: "events", component: EventPage, meta: { requiresAuth: true } },
    { path: "/messages", name: "messages", component: DirectMessages, meta: { requiresAuth: true } },
    { path: "/events/create", name: "CreateEvent", component: CreateEvent, meta: { requiresAuth: true } },
    { path: "/events/:id", name: "EventDetail", component: EventDetail, props: true, meta: { requiresAuth: true } },
    { path: "/posts/create", name: "CreatePost", component: CreatePost, meta: { requiresAuth: true } },
    { path: "/awards/create", name: "CreateAward", component: CreateAward, meta: { requiresAuth: true } },
    { path: "/videos/create", name: "UploadVideo", component: UploadVideo, meta: { requiresAuth: true } },

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const isAuthenticated = Boolean(token)

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && isAuthenticated) {
    return { name: 'home' }
  }

  return true
})

export default router
