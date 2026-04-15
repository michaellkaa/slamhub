import { createRouter, createWebHistory } from "vue-router";

const routes = [
    
    { path: "/", name: "home", component: () => import("./pages/HomePage.vue"), meta: { requiresAuth: true } },
    { path: "/awards", name: "awards", component: () => import("./pages/AwardPages.vue"), meta: { requiresAuth: true } },
    { path: "/login", name: "login", component: () => import("./pages/LoginPage.vue"), meta: { guestOnly: true } },
    { path: "/register", name: "register", component: () => import("./pages/RegisterPage.vue"), meta: { guestOnly: true } },
    { path: "/auth/google/callback", name: "auth.google.callback", component: () => import("./pages/AuthGoogleCallback.vue"), meta: { guestOnly: true } },
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
    { path: "/profile/:username", name: "profile.detail", component: () => import("./pages/ProfileDetail.vue"), props: true, meta: { requiresAuth: true } },
    { path: "/settings", name: "settings", component: () => import("./pages/ProfileSettings.vue"), meta: { requiresAuth: true } },
    { path: "/events", name: "events", component: () => import("./pages/EventPage.vue"), meta: { requiresAuth: true } },
    { path: "/messages", name: "messages", component: () => import("./pages/DirectMessages.vue"), meta: { requiresAuth: true } },
    { path: "/events/create", name: "CreateEvent", component: () => import("./pages/CreateEvent.vue"), meta: { requiresAuth: true } },
    { path: "/events/:id", name: "EventDetail", component: () => import("./pages/EventDetail.vue"), props: true, meta: { requiresAuth: true } },
    { path: "/events/:id/vote", name: "EventVote", component: () => import("./pages/EventVote.vue"), props: true, meta: { requiresAuth: true } },
    { path: "/events/:id/voting/host", name: "EventVoteHost", component: () => import("./pages/EventVoteHost.vue"), props: true, meta: { requiresAuth: true } },
    { path: "/posts/create", name: "CreatePost", component: () => import("./pages/CreatePost.vue"), meta: { requiresAuth: true } },
    { path: "/awards/create", name: "CreateAward", component: () => import("./pages/CreateAward.vue"), meta: { requiresAuth: true } },
    { path: "/videos/create", name: "UploadVideo", component: () => import("./pages/UploadVideo.vue"), meta: { requiresAuth: true } },

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
