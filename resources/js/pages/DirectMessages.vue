<template>
  <div class="bg-[#0f0f12] w-screen h-screen flex flex-col lg:flex-row overflow-hidden">

    <div class="lg:h-full lg:w-28 w-full h-36 fixed bottom-0 lg:static z-10">
      <SideNav :activeNav="activeNav" @navigate="handleNavigate" />
    </div>

    <div class="flex-1 flex flex-col overflow-y-auto pb-28 lg:pb-0">
      <div class="flex w-full h-full overflow-hidden">

        <div class="w-72 bg-[#141418] border-r border-[#1f1f22] flex flex-col">
          <div class="h-16 border-b border-[#1f1f22] flex items-center px-4">
            <div class="h-6 w-40 bg-[#1d1d21] rounded"></div>
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
                  <div class="text-gray-400 text-sm">{{ user.status || 'Online' }}</div>
                </div>
              </div>
            </template>

            <template v-else>
  <div class="flex flex-col items-center justify-center mt-12 space-y-4 text-gray-400">
    <p class="text-center">Zkuste napsat lidem, které sledujete</p>
  </div>
</template>

<div v-if="suggestedUsers.length > 0" class="mt-6 w-full space-y-3">
  <div class="text-gray-400 text-sm mb-2 px-1">Lidé které sledujete</div>
                <div 
                v-for="user in suggestedUsers" 
                :key="user.id" 
                class="flex items-center justify-between p-2 bg-[#1d1d21] rounded hover:bg-[#2a2a30] cursor-pointer"
                @click="selectUser(user)"
              >
                <div class="flex items-center space-x-3">
                  <img :src="user.profile_pic_url || placeholderAvatar" class="h-10 w-10 rounded-full object-cover" />
                  <div class="text-white">{{ user.name }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="flex-1 flex flex-col">

          <div class="h-16 border-b border-[#1f1f22] flex items-center px-6 space-x-4">
            <img v-if="selectedUser" :src="selectedUser.profile_pic_url || placeholderAvatar" class="h-12 w-12 rounded-full object-cover" />
            <div>
              <div class="text-white font-medium mb-1">{{ selectedUser?.name || 'Select a user' }}</div>
              <div class="text-gray-400 text-sm">{{ selectedUser?.status || '' }}</div>
            </div>
          </div>

          <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
            <div 
              v-for="message in messages" 
              :key="message.id" 
              :class="{'flex justify-end': message.sender_id === currentUser.id, 'flex space-x-3 items-start': message.sender_id !== currentUser.id }"
            >
              <img v-if="message.sender_id !== currentUser.id" :src="message.sender.profile_pic_url || placeholderAvatar" class="h-10 w-10 rounded-full object-cover" />
              <div :class="message.sender_id === currentUser.id ? 'bg-[#1d1d21] text-white p-3 rounded-lg max-w-md' : 'bg-[#2a2a30] text-white p-3 rounded-lg max-w-md'">
                {{ message.body }}
              </div>
            </div>
          </div>

          <div class="h-20 border-t border-[#1f1f22] flex items-center px-6 space-x-4">
            <input 
              v-model="newMessage" 
              @keyup.enter="sendMessage" 
              placeholder="Type a message..." 
              class="flex-1 h-12 px-4 rounded-lg bg-[#1d1d21] text-white focus:outline-none" 
            />
            <button @click="sendMessage" class="h-12 w-24 bg-[#1d1d21] rounded-lg text-white">Send</button>
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

axios.defaults.baseURL = "http://127.0.0.1:8000"
axios.defaults.withCredentials = true
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"

onMounted(async () => {
  //await axios.get('/sanctum/csrf-cookie')
  await fetchMe()
  await fetchFollowing()
  await fetchUsers()
  startRealtimePolling()
})

onBeforeUnmount(() => {
  stopRealtimePolling()
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

const chatContainer = ref(null)
let realtimeTimer = null

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
    const response = await axios.get('/api/users')
    const allUsers = Array.isArray(response.data) ? response.data : []

    const convResponse = await axios.get('/api/conversations')

    const contactIds = convResponse.data.map(c => {
  const otherUser = c.users.find(u => u.id !== currentUser.value.id)
  return otherUser?.id
})

    contacts.value = allUsers.filter(u => contactIds.includes(u.id))

    if (contacts.value.length > 0 && !selectedUser.value) {
      selectUser(contacts.value[0])
    }

  } catch (err) {
    console.error('Error fetching users:', err)
  }
}



async function fetchMessages(userId) {
  try {
    const convResponse = await axios.get(`/api/conversations/${userId}/messages`)
    messages.value = convResponse.data
    scrollToBottom()
  } catch (err) {
    console.error('Error fetching messages:', err)
  }
}


async function selectUser(user) {
  selectedUser.value = user

  try {
    const convRes = await axios.post(`/api/conversations/${user.id}`)
    const conversation = convRes.data
    selectedUser.value.conversation_id = conversation.id

    const messagesRes = await axios.get(`/api/conversations/${conversation.id}/messages`)
    messages.value = Array.isArray(messagesRes.data) ? messagesRes.data : []
    scrollToBottom()
  } catch (err) {
    console.error('Error selecting user / fetching conversation:', err)
  }
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

async function fetchFollowing() {
  try {
    const response = await axios.get('/api/following')
    suggestedUsers.value = response.data  
  } catch (err) {
    console.error(err)
  }
}

async function refreshOpenConversation() {
  if (!selectedUser.value?.conversation_id) return

  try {
    const response = await axios.get(`/api/conversations/${selectedUser.value.conversation_id}/messages`)
    const nextMessages = Array.isArray(response.data) ? response.data : []

    const hasChanged =
      nextMessages.length !== messages.value.length ||
      nextMessages[nextMessages.length - 1]?.id !== messages.value[messages.value.length - 1]?.id

    if (hasChanged) {
      messages.value = nextMessages
      scrollToBottom()
    }
  } catch (err) {
    console.error('Error refreshing conversation:', err)
  }
}

function startRealtimePolling() {
  stopRealtimePolling()
  realtimeTimer = setInterval(refreshOpenConversation, 1200)
}

function stopRealtimePolling() {
  if (realtimeTimer) {
    clearInterval(realtimeTimer)
    realtimeTimer = null
  }
}

</script>