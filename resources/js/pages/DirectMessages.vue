<template>
  <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">

    <div class="lg:h-full lg:w-28 w-full fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex flex-col overflow-hidden pb-28 lg:pb-0 min-h-0">
      <div class="flex w-full h-full overflow-hidden min-h-0">

        <div
          :class="[
            'w-full lg:w-72 bg-[#141418] border-r border-[#1f1f22] flex flex-col',
            selectedUser ? 'hidden lg:flex' : 'flex',
          ]"
        >
          <div class="h-16 border-b border-[#1f1f22] flex items-center justify-between px-4">
            <div class="text-white font-semibold">Direct messages</div>
            <button
              type="button"
              class="px-3 py-1.5 rounded-lg bg-[#1d1d21] hover:bg-[#2a2a30] text-white text-sm"
              @click="showNewChat = !showNewChat"
            >
              New chat
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-4 space-y-4">

            <template v-if="contacts.length > 0">
              <div 
                v-for="user in contacts" 
                :key="user.id" 
                class="flex items-center space-x-3 cursor-pointer hover:bg-[#1a1a1d] p-2 rounded" 
                @click="selectUser(user)"
              >
                <img :src="user.profile_pic_url || placeholderAvatar" alt="" class="h-12 w-12 rounded-full object-cover" />
                <div class="flex-1 space-y-1">
                  <div class="text-white font-medium">{{ user.name }}</div>
                  <!--<div class="text-gray-400 text-sm">{{ user.status || 'Online' }}</div>-->
                </div>
              </div>
            </template>

            <template v-else>
              <div class="flex flex-col items-center justify-center mt-12 space-y-4 text-gray-400">
                <p class="text-center">Žádné konverzace. Začni přes New chat.</p>
              </div>
            </template>

            <div v-if="showNewChat && suggestedUsers.length > 0" class="mt-2 w-full space-y-3">
              <div class="text-gray-400 text-sm mb-2 px-1">People you follow</div>
                <div 
                v-for="user in suggestedUsers" 
                :key="user.id" 
                class="flex items-center justify-between p-2 bg-[#1d1d21] rounded hover:bg-[#2a2a30] cursor-pointer"
                @click="startNewChat(user)"
              >
                <div class="flex items-center space-x-3">
                  <img :src="user.profile_pic_url || placeholderAvatar" class="h-10 w-10 rounded-full object-cover" />
                  <div class="text-white">{{ user.name }}</div>
                </div>
                <span class="text-xs text-white/60">Message</span>
              </div>
            </div>

            <div v-else-if="showNewChat" class="text-sm text-white/60 bg-[#1d1d21] rounded p-3">
              Nobody in your following list yet.
            </div>

          </div>
        </div>

        <div :class="['flex-1 flex flex-col min-h-0 h-full', selectedUser ? 'flex' : 'hidden lg:flex']">

          <div class="h-16 border-b border-[#1f1f22] flex items-center px-4 lg:px-6 space-x-3 lg:space-x-4">
            <button
              v-if="selectedUser"
              @click="backToList"
              class="lg:hidden text-white/80 hover:text-white px-2 py-1 rounded bg-white/5"
            >
              ←
            </button>
            <img v-if="selectedUser" :src="selectedUser.profile_pic_url || placeholderAvatar" class="h-12 w-12 rounded-full object-cover" />
            <div>
              <div class="text-white font-medium mb-1">{{ selectedUser?.name || 'Select a user' }}</div>
              <div class="text-gray-400 text-sm">{{ selectedUser?.status || '' }}</div>
            </div>
          </div>

          <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 lg:p-6 space-y-4 min-h-0">
            <div v-if="!selectedUser" class="h-full flex items-center justify-center text-gray-400 text-sm">
              Vyberte konverzaci vlevo.
            </div>
            <div 
              v-for="message in messages" 
              :key="message.id" 
              :class="{'flex justify-end': message.sender_id === currentUser?.id, 'flex space-x-3 items-start': message.sender_id !== currentUser?.id }"
            >
              <img v-if="message.sender_id !== currentUser?.id" :src="message.sender.profile_pic_url || placeholderAvatar" class="h-10 w-10 rounded-full object-cover" />
              <div :class="message.sender_id === currentUser?.id ? 'bg-[#1d1d21] text-white p-3 rounded-lg max-w-[80%] lg:max-w-md break-words' : 'bg-[#2a2a30] text-white p-3 rounded-lg max-w-[80%] lg:max-w-md break-words'">
                {{ message.body }}
              </div>
            </div>
          </div>

          <div class="h-20 shrink-0 border-t border-[#1f1f22] flex items-center px-4 lg:px-6 space-x-3 lg:space-x-4">
            <input 
              v-model="newMessage" 
              @keyup.enter="sendMessage" 
              placeholder="Type a message..." 
              class="flex-1 h-12 px-4 rounded-lg bg-[#1d1d21] text-white focus:outline-none" 
            />
            <button @click="sendMessage" class="h-12 px-4 lg:w-24 bg-[#1d1d21] rounded-lg text-white shrink-0">Send</button>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue'
import SideNav from '../components/SideNav.vue'

import axios from 'axios'

axios.defaults.baseURL = window.location.origin
axios.defaults.withCredentials = true
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"

onMounted(async () => {
  //await axios.get('/sanctum/csrf-cookie')
  await fetchMe()
  await fetchFollowing()
  await fetchUsers()
  startRealtimePolling()
  window.addEventListener('focus', handleFocus)
  window.addEventListener('blur', handleBlur)
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onBeforeUnmount(() => {
  stopRealtimePolling()
  window.removeEventListener('focus', handleFocus)
  window.removeEventListener('blur', handleBlur)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})

const activeNav = ref('messages')
function handleNavigate(nav) { activeNav.value = nav }

const placeholderAvatar = 'https://i.pravatar.cc/150?img=1'

const currentUser = ref(null)  

const contacts = ref([])
const suggestedUsers = ref([])
const selectedUser = ref(null)
const messages = ref([])
const newMessage = ref('')
const showNewChat = ref(false)

const chatContainer = ref(null)
let realtimeTimer = null
let refreshInFlight = false
const FAST_POLL_MS = 2500
const SLOW_POLL_MS = 10000
const pageVisible = ref(true)

async function fetchMe() {
  try {
    const response = await axios.get('/api/me')
    currentUser.value = response.data
  } catch (err) {
    console.error('Error fetching current user:', err)
  }
}

async function fetchUsers() {
  try {
    const convResponse = await axios.get('/api/conversations')
    const conversations = Array.isArray(convResponse.data) ? convResponse.data : []

    const currentUserId = currentUser.value?.id
    contacts.value = conversations
      .map((conversation) => {
        const otherUser = conversation.users.find((u) => u.id !== currentUserId)
        if (!otherUser) return null
        return {
          ...otherUser,
          conversation_id: conversation.id,
        }
      })
      .filter(Boolean)

  } catch (err) {
    console.error('Error fetching users:', err)
  }
}


async function selectUser(user) {
  selectedUser.value = user
  showNewChat.value = false

  try {
    if (!selectedUser.value.conversation_id) {
      const convRes = await axios.post(`/api/conversations/${user.id}`)
      selectedUser.value.conversation_id = convRes.data?.id
    }

    await loadConversationMessages(selectedUser.value.conversation_id)
    scrollToBottom()
  } catch (err) {
    console.error('Error selecting user / fetching conversation:', err)
  }
}

async function startNewChat(user) {
  await selectUser(user)
}

function backToList() {
  selectedUser.value = null
  messages.value = []
}

async function sendMessage() {
  if (!newMessage.value.trim() || !selectedUser.value || !selectedUser.value.conversation_id) return

  try {
    const response = await axios.post(
      `/api/conversations/${selectedUser.value.conversation_id}/messages`,
      { body: newMessage.value }
    )

    messages.value.push(response.data)

    newMessage.value = ''
    scrollToBottom()
  } catch (err) {
    console.error('Error sending message:', err)
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  })
}

async function loadConversationMessages(conversationId, options = {}) {
  if (!conversationId) return

  const { sinceId = null } = options
  try {
    const params = sinceId ? { since_id: sinceId } : { limit: 60 }
    const response = await axios.get(`/api/conversations/${conversationId}/messages`, { params })
    const incoming = Array.isArray(response.data) ? response.data : []

    if (!sinceId) {
      messages.value = incoming
      return
    }

    if (!incoming.length) return
    messages.value = [...messages.value, ...incoming]
  } catch (err) {
    console.error('Error loading messages:', err)
  }
}

async function fetchFollowing() {
  try {
    const response = await axios.get('/api/following')
    suggestedUsers.value = response.data  
  } catch (err) {
    console.error(err)
  }
}

async function refreshOpenConversation() {
  if (!selectedUser.value?.conversation_id || refreshInFlight) return

  refreshInFlight = true
  try {
    const lastMessageId = messages.value[messages.value.length - 1]?.id || null
    const previousCount = messages.value.length
    await loadConversationMessages(selectedUser.value.conversation_id, { sinceId: lastMessageId })

    if (messages.value.length > previousCount) {
      scrollToBottom()
    }
  } catch (err) {
    console.error('Error refreshing conversation:', err)
  } finally {
    refreshInFlight = false
  }
}

function startRealtimePolling() {
  stopRealtimePolling()
  const interval = pageVisible.value ? FAST_POLL_MS : SLOW_POLL_MS
  realtimeTimer = setInterval(refreshOpenConversation, interval)
}

function stopRealtimePolling() {
  if (realtimeTimer) {
    clearInterval(realtimeTimer)
    realtimeTimer = null
  }
}

function handleVisibilityChange() {
  pageVisible.value = document.visibilityState === 'visible'
  startRealtimePolling()
}

function handleFocus() {
  pageVisible.value = true
  startRealtimePolling()
}

function handleBlur() {
  pageVisible.value = false
  startRealtimePolling()
}

</script>