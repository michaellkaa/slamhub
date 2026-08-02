<template>
  <div class="min-h-screen bg-app text-app px-3 sm:px-4 md:px-6 py-6 md:py-10 pb-28 lg:pb-10">
    <div class="w-full max-w-6xl mx-auto">
      <div class="mb-6 md:mb-8">
        <button type="button" @click="goBack" class="text-sm text-app-muted hover:text-app transition mb-3">
          ← Zpět do nastavení
        </button>
        <h1 class="text-xl sm:text-2xl font-semibold">Admin dashboard</h1>
        <p class="text-app-muted text-sm mt-1">Správa uživatelů, obsahu a přehled platformy.</p>
      </div>

      <div v-if="forbidden" class="rounded-xl border border-app bg-surface p-6 text-sm text-red-400">
        Nemáš oprávnění k admin dashboardu.
      </div>

      <template v-else>
        <nav class="flex gap-1 overflow-x-auto no-scrollbar mb-6 border-b border-app pb-1 -mx-1 px-1">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            @click="activeTab = tab.key"
            class="px-3 py-2 text-sm font-medium rounded-t-lg transition whitespace-nowrap shrink-0"
            :class="activeTab === tab.key
              ? 'text-app border-b-2 border-pink-500'
              : 'text-app-muted hover:text-app'"
          >
            {{ tab.label }}
          </button>
        </nav>

        <div v-if="loadingOverview && activeTab === 'overview'" class="text-app-muted text-sm">Načítám…</div>

        <section v-else-if="activeTab === 'overview'" class="space-y-6">
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div v-for="card in overviewCards" :key="card.label" class="rounded-xl border border-app bg-surface p-3 sm:p-4">
              <div class="text-[11px] sm:text-xs text-app-muted">{{ card.label }}</div>
              <div class="text-xl sm:text-2xl font-semibold mt-1">{{ card.value }}</div>
            </div>
          </div>

          <div class="rounded-xl border border-app bg-surface p-4">
            <h2 class="text-sm font-semibold mb-3">Uživatelé podle role</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
              <div v-for="role in roles" :key="role" class="rounded-lg bg-app px-3 py-2">
                <div class="text-[11px] text-app-muted uppercase">{{ role }}</div>
                <div class="text-lg font-semibold">{{ overview?.users_by_role?.[role] || 0 }}</div>
              </div>
            </div>
          </div>
        </section>

        <section v-else-if="activeTab === 'users'" class="space-y-4">
          <div class="flex flex-col gap-3">
            <input
              v-model="userFilters.q"
              type="search"
              placeholder="Hledat jméno, username, email…"
              class="w-full h-10 px-3 rounded-md bg-surface text-app border border-app focus:outline-none"
              @keyup.enter="loadUsers(1)"
            />
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <select v-model="userFilters.role" class="h-10 px-3 rounded-md bg-surface text-app border border-app" @change="loadUsers(1)">
                <option value="">Všechny role</option>
                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
              </select>
              <select v-model="userFilters.banned" class="h-10 px-3 rounded-md bg-surface text-app border border-app" @change="loadUsers(1)">
                <option value="">Ban: všichni</option>
                <option value="1">Jen zabanovaní</option>
                <option value="0">Jen aktivní</option>
              </select>
              <button type="button" class="h-10 px-4 rounded-md bg-pink-500 text-white text-sm font-semibold" @click="loadUsers(1)">
                Hledat
              </button>
            </div>
          </div>

          <div v-if="loadingUsers" class="text-app-muted text-sm">Načítám uživatele…</div>
          <div v-else class="space-y-3">
            <div
              v-for="user in users"
              :key="user.id"
              class="rounded-xl border border-app bg-surface p-4"
            >
              <div class="flex items-center gap-3 mb-3">
                <img :src="user.profile_pic_url" class="w-10 h-10 rounded-full object-cover shrink-0" alt="" />
                <div class="min-w-0 flex-1">
                  <div class="font-medium truncate">{{ user.name }}</div>
                  <div class="text-xs text-app-muted truncate">@{{ user.username }} · {{ user.points }} bodů</div>
                </div>
                <a :href="`/profile/${user.username}`" class="text-xs text-pink-400 hover:text-pink-300 shrink-0">Profil</a>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block text-xs text-app-muted">
                  Role
                  <select
                    :value="user.role"
                    class="mt-1 w-full h-10 px-2 rounded-md bg-app border border-app text-app text-sm"
                    :disabled="savingUserId === user.id"
                    @change="updateUserRole(user, $event.target.value)"
                  >
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                  </select>
                </label>
                <div class="flex flex-col justify-end">
                  <span class="text-xs text-app-muted mb-1">Stav</span>
                  <button
                    type="button"
                    class="h-10 px-3 rounded-md text-sm font-semibold transition"
                    :class="user.is_banned ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300'"
                    :disabled="savingUserId === user.id"
                    @click="toggleBan(user)"
                  >
                    {{ user.is_banned ? 'Zabanován' : 'Aktivní' }}
                  </button>
                </div>
              </div>
            </div>

            <div v-if="usersMeta.last_page > 1" class="flex items-center justify-between px-1 py-2 text-xs text-app-muted">
              <button type="button" :disabled="usersMeta.current_page <= 1" class="disabled:opacity-40" @click="loadUsers(usersMeta.current_page - 1)">← Předchozí</button>
              <span>{{ usersMeta.current_page }} / {{ usersMeta.last_page }}</span>
              <button type="button" :disabled="usersMeta.current_page >= usersMeta.last_page" class="disabled:opacity-40" @click="loadUsers(usersMeta.current_page + 1)">Další →</button>
            </div>
          </div>
          <p v-if="userError" class="text-sm text-red-400">{{ userError }}</p>
        </section>

        <section v-else-if="activeTab === 'posts'" class="space-y-4">
          <div class="flex flex-col sm:flex-row gap-3">
            <select v-model="postFilters.hidden" class="h-10 px-3 rounded-md bg-surface text-app border border-app" @change="loadPosts(1)">
              <option value="">Všechny příspěvky</option>
              <option value="1">Jen skryté</option>
            </select>
            <button type="button" class="h-10 px-4 rounded-md bg-surface-hover text-sm" @click="loadPosts(1)">Obnovit</button>
          </div>
          <div v-if="loadingPosts" class="text-app-muted text-sm">Načítám…</div>
          <div v-else class="space-y-3">
            <div v-for="post in posts" :key="post.id" class="rounded-xl border border-app bg-surface p-4">
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-xs text-app-muted mb-1">
                    {{ post.user?.name || 'Neznámý' }} · @{{ post.user?.username }}
                  </div>
                  <p class="text-sm whitespace-pre-wrap break-words">{{ post.body }}</p>
                </div>
                <button
                  type="button"
                  class="shrink-0 self-start px-3 py-1.5 rounded-md text-xs font-semibold"
                  :class="post.status === 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300'"
                  @click="togglePostVisibility(post)"
                >
                  {{ post.status === 0 ? 'Zobrazit' : 'Skrýt' }}
                </button>
              </div>
            </div>
          </div>
        </section>

        <section v-else-if="activeTab === 'videos'" class="space-y-4">
          <div class="flex flex-col sm:flex-row gap-3">
            <select v-model="videoFilters.status" class="h-10 px-3 rounded-md bg-surface text-app border border-app" @change="loadVideos(1)">
              <option value="">Všechny statusy</option>
              <option value="public">public</option>
              <option value="unlisted">unlisted</option>
              <option value="private">private</option>
            </select>
            <button type="button" class="h-10 px-4 rounded-md bg-surface-hover text-sm" @click="loadVideos(1)">Obnovit</button>
          </div>
          <div v-if="loadingVideos" class="text-app-muted text-sm">Načítám…</div>
          <div v-else class="space-y-3">
            <div
              v-for="video in videos"
              :key="video.id"
              class="rounded-xl border border-app bg-surface p-4"
            >
              <div class="font-medium text-sm break-words">{{ video.title || 'Bez názvu' }}</div>
              <div class="text-xs text-app-muted mt-1 mb-3">@{{ video.user?.username }}</div>
              <label class="block text-xs text-app-muted">
                Status
                <select
                  :value="video.status"
                  class="mt-1 w-full h-10 px-2 rounded-md bg-app border border-app text-app text-sm"
                  @change="updateVideoStatus(video, $event.target.value)"
                >
                  <option value="public">public</option>
                  <option value="unlisted">unlisted</option>
                  <option value="private">private</option>
                </select>
              </label>
            </div>
          </div>
        </section>

        <section v-else-if="activeTab === 'events'" class="space-y-4">
          <div v-if="loadingEvents" class="text-app-muted text-sm">Načítám…</div>
          <div v-else class="space-y-3">
            <a
              v-for="event in events"
              :key="event.id"
              :href="`/events/${event.id}`"
              class="block rounded-xl border border-app bg-surface p-4 hover:bg-surface-hover transition"
            >
              <div class="font-medium break-words">{{ event.title || 'Bez názvu' }}</div>
              <div class="text-xs text-app-muted mt-1">
                {{ event.location || 'Bez místa' }}
                <span v-if="event.user"> · {{ event.user.name }}</span>
              </div>
            </a>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const roles = ['user', 'performer', 'organizer', 'moderator', 'admin']
const tabs = [
  { key: 'overview', label: 'Přehled' },
  { key: 'users', label: 'Uživatelé' },
  { key: 'posts', label: 'Příspěvky' },
  { key: 'videos', label: 'Videa' },
  { key: 'events', label: 'Eventy' },
]

const activeTab = ref('overview')
const forbidden = ref(false)

const overview = ref(null)
const loadingOverview = ref(false)

const users = ref([])
const usersMeta = ref({ current_page: 1, last_page: 1 })
const loadingUsers = ref(false)
const savingUserId = ref(null)
const userError = ref('')
const userFilters = ref({ q: '', role: '', banned: '' })

const posts = ref([])
const loadingPosts = ref(false)
const postFilters = ref({ hidden: '' })

const videos = ref([])
const loadingVideos = ref(false)
const videoFilters = ref({ status: '' })

const events = ref([])
const loadingEvents = ref(false)

const overviewCards = computed(() => [
  { label: 'Uživatelé', value: overview.value?.users_total ?? '—' },
  { label: 'Zabanovaní', value: overview.value?.users_banned ?? '—' },
  { label: 'Příspěvky', value: overview.value?.posts_total ?? '—' },
  { label: 'Videa', value: overview.value?.videos_total ?? '—' },
  { label: 'Eventy', value: overview.value?.events_total ?? '—' },
  { label: 'Skryté příspěvky', value: overview.value?.posts_hidden ?? '—' },
])

const goBack = () => router.push('/settings')

const ensureAdminAccess = async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return false
  }
  axios.defaults.headers.common.Authorization = `Bearer ${token}`

  try {
    const { data } = await axios.get('/api/me')
    localStorage.setItem('user', JSON.stringify({
      ...JSON.parse(localStorage.getItem('user') || '{}'),
      ...data,
    }))
    if (data.role !== 'admin') {
      forbidden.value = true
      return false
    }
    return true
  } catch {
    router.push('/login')
    return false
  }
}

const loadOverview = async () => {
  loadingOverview.value = true
  try {
    const { data } = await axios.get('/api/admin/overview')
    overview.value = data
  } catch (err) {
    if (err.response?.status === 403) forbidden.value = true
  } finally {
    loadingOverview.value = false
  }
}

const loadUsers = async (page = 1) => {
  loadingUsers.value = true
  userError.value = ''
  try {
    const { data } = await axios.get('/api/admin/users', {
      params: {
        page,
        q: userFilters.value.q || undefined,
        role: userFilters.value.role || undefined,
        banned: userFilters.value.banned || undefined,
      },
    })
    users.value = data.data || []
    usersMeta.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
    }
  } catch (err) {
    userError.value = err.response?.data?.message || 'Nepodařilo se načíst uživatele.'
  } finally {
    loadingUsers.value = false
  }
}

const updateUserRole = async (user, role) => {
  savingUserId.value = user.id
  userError.value = ''
  try {
    const { data } = await axios.put(`/api/admin/users/${user.id}`, { role })
    Object.assign(user, data)
  } catch (err) {
    userError.value = err.response?.data?.message || 'Nepodařilo se změnit roli.'
    await loadUsers(usersMeta.value.current_page)
  } finally {
    savingUserId.value = null
  }
}

const toggleBan = async (user) => {
  savingUserId.value = user.id
  userError.value = ''
  try {
    const { data } = await axios.put(`/api/admin/users/${user.id}`, { is_banned: !user.is_banned })
    Object.assign(user, data)
  } catch (err) {
    userError.value = err.response?.data?.message || 'Nepodařilo se změnit ban.'
  } finally {
    savingUserId.value = null
  }
}

const loadPosts = async (page = 1) => {
  loadingPosts.value = true
  try {
    const { data } = await axios.get('/api/admin/posts', {
      params: {
        page,
        hidden: postFilters.value.hidden || undefined,
      },
    })
    posts.value = data.data || []
  } finally {
    loadingPosts.value = false
  }
}

const togglePostVisibility = async (post) => {
  const next = post.status === 0 ? 1 : 0
  const { data } = await axios.put(`/api/admin/posts/${post.id}`, { status: next })
  post.status = data.status
}

const loadVideos = async (page = 1) => {
  loadingVideos.value = true
  try {
    const { data } = await axios.get('/api/admin/videos', {
      params: {
        page,
        status: videoFilters.value.status || undefined,
      },
    })
    videos.value = data.data || []
  } finally {
    loadingVideos.value = false
  }
}

const updateVideoStatus = async (video, status) => {
  const { data } = await axios.put(`/api/admin/videos/${video.id}`, { status })
  video.status = data.status
}

const loadEvents = async () => {
  loadingEvents.value = true
  try {
    const { data } = await axios.get('/api/admin/events')
    events.value = data.data || []
  } finally {
    loadingEvents.value = false
  }
}

watch(activeTab, (tab) => {
  if (forbidden.value) return
  if (tab === 'overview') loadOverview()
  if (tab === 'users') loadUsers()
  if (tab === 'posts') loadPosts()
  if (tab === 'videos') loadVideos()
  if (tab === 'events') loadEvents()
})

onMounted(async () => {
  const ok = await ensureAdminAccess()
  if (ok) loadOverview()
})
</script>
