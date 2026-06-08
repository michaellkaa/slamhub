import { createRouter, createWebHistory } from "vue-router";

const routes = [

  { path: "/", name: "home", component: () => import("./Pages/HomePage.vue"), meta: { requiresAuth: true } },
  { path: "/awards", name: "awards", component: () => import("./Pages/AwardPages.vue"), meta: { requiresAuth: true } },
  { path: "/login", name: "login", component: () => import("./Pages/LoginPage.vue"), meta: { guestOnly: true } },
  { path: "/register", name: "register", component: () => import("./Pages/RegisterPage.vue"), meta: { guestOnly: true } },
  { path: "/verify-email", name: "verify-email", component: () => import("./Pages/VerifyEmailPage.vue"), meta: { guestOnly: true } },
  { path: "/auth/google/callback", name: "auth.google.callback", component: () => import("./Pages/AuthGoogleCallback.vue"), meta: { guestOnly: true } },
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
  { path: "/profile/:username", name: "profile.detail", component: () => import("./Pages/ProfileDetail.vue"), props: true, meta: { requiresAuth: true } },
  { path: "/settings", name: "settings", component: () => import("./Pages/ProfileSettings.vue"), meta: { requiresAuth: true } },
  { path: "/events", name: "events", component: () => import("./Pages/EventPage.vue"), meta: { requiresAuth: true } },
  { path: "/messages", name: "messages", component: () => import("./Pages/DirectMessages.vue"), meta: { requiresAuth: true } },
  { path: "/events/create", name: "CreateEvent", component: () => import("./Pages/CreateEvent.vue"), meta: { requiresAuth: true } },
  { path: "/events/:id", name: "EventDetail", component: () => import("./Pages/EventDetail.vue"), props: true, meta: { requiresAuth: true } },
  { path: "/events/:id/vote", name: "EventVote", component: () => import("./Pages/EventVote.vue"), props: true, meta: { requiresAuth: true } },
  { path: "/events/:id/voting/host", name: "EventVoteHost", component: () => import("./Pages/EventVoteHost.vue"), props: true, meta: { requiresAuth: true } },
  { path: "/events/:id/league/host", name: "EventLeagueHost", component: () => import("./Pages/EventLeagueHost.vue"), props: true, meta: { requiresAuth: true } },
  { path: "/posts/create", name: "CreatePost", component: () => import("./Pages/CreatePost.vue"), meta: { requiresAuth: true } },
  { path: "/awards/create", name: "CreateAward", component: () => import("./Pages/CreateAward.vue"), meta: { requiresAuth: true } },
  { path: "/videos/create", name: "UploadVideo", component: () => import("./Pages/UploadVideo.vue"), meta: { requiresAuth: true } },
  {
    path: "/events/:id/edit",
    name: "EditEvent",
    component: () => import("./Pages/CreateEvent.vue"),
    props: true,
    meta: { requiresAuth: true }
  }

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
