import { createRouter, createWebHistory } from "vue-router";

const routes = [

  { path: "/", name: "home", component: () => import("./pages/HomePage.vue"), meta: { title: "SlamHub", requiresAuth: true } },
  { path: "/awards", name: "awards", component: () => import("./pages/AwardPages.vue"), meta: { title: "Ocenění", requiresAuth: true } },
  { path: "/login", name: "login", component: () => import("./pages/LoginPage.vue"), meta: { title: "Přihlášení", guestOnly: true } },
  { path: "/register", name: "register", component: () => import("./pages/RegisterPage.vue"), meta: { title: "Registrace", guestOnly: true } },
  { path: "/verify-email", name: "verify-email", component: () => import("./pages/VerifyEmailPage.vue"), meta: { title: "Ověření e-mailu", guestOnly: true } },
  { path: "/auth/google/callback", name: "auth.google.callback", component: () => import("./pages/AuthGoogleCallback.vue"), meta: { title: "Přihlášení Google", guestOnly: true } },
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
  { path: "/profile/:username", name: "profile.detail", component: () => import("./pages/ProfileDetail.vue"), props: true, meta: { title: "Profil", requiresAuth: true } },
  { path: "/settings", name: "settings", component: () => import("./pages/ProfileSettings.vue"), meta: { title: "Nastavení", requiresAuth: true } },
  { path: "/admin", name: "admin", component: () => import("./pages/AdminDashboard.vue"), meta: { title: "Admin", requiresAuth: true, requiresAdmin: true } },
  { path: "/events", name: "events", component: () => import("./pages/EventPage.vue"), meta: { title: "Události", requiresAuth: true } },
  { path: "/messages", name: "messages", component: () => import("./pages/DirectMessages.vue"), meta: { title: "Zprávy", requiresAuth: true } },
  { path: "/events/create", name: "CreateEvent", component: () => import("./pages/CreateEvent.vue"), meta: { title: "Vytvořit událost", requiresAuth: true } },
  { path: "/events/:id", name: "EventDetail", component: () => import("./pages/EventDetail.vue"), props: true, meta: { title: "Událost", requiresAuth: true } },
  { path: "/events/:id/vote", name: "EventVote", component: () => import("./pages/EventVote.vue"), props: true, meta: { title: "Hlasování", requiresAuth: true } },
  { path: "/events/:id/voting/host", name: "EventVoteHost", component: () => import("./pages/EventVoteHost.vue"), props: true, meta: { title: "Hostování hlasování", requiresAuth: true } },
  { path: "/events/:id/league/host", name: "EventLeagueHost", component: () => import("./pages/EventLeagueHost.vue"), props: true, meta: { title: "Ligová tabulka", requiresAuth: true } },
  { path: "/posts/create", name: "CreatePost", component: () => import("./pages/CreatePost.vue"), meta: { title: "Nový příspěvek", requiresAuth: true } },
  { path: "/awards/create", name: "CreateAward", component: () => import("./pages/CreateAward.vue"), meta: { title: "Nové ocenění", requiresAuth: true } },
  { path: "/videos/create", name: "UploadVideo", component: () => import("./pages/UploadVideo.vue"), meta: { title: "Nahrát video", requiresAuth: true } },
  {
    path: "/events/:id/edit",
    name: "EditEvent",
    component: () => import("./pages/CreateEvent.vue"),
    props: true,
    meta: { title: "Upravit událost", requiresAuth: true }
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

  if (to.meta.requiresAdmin && isAuthenticated) {
    try {
      const user = JSON.parse(localStorage.getItem('user') || 'null')
      if (user?.role !== 'admin') {
        return { name: 'settings' }
      }
    } catch {
      return { name: 'settings' }
    }
  }

  return true
})

router.afterEach((to) => {
  const baseTitle = 'SlamHub'
  const pageTitle = to.meta.title || ''
  document.title = pageTitle && pageTitle !== baseTitle
    ? `${baseTitle} | ${pageTitle}`
    : baseTitle
})

export default router
