<template>
  <div class="chat-view">
    <!-- Admin Chat List -->
    <template v-if="isAdmin">
      <div class="chat-container">
        <div class="chat-sidebar">
          <AdminChatList 
            :chats="chats"
            :selectedChatId="selectedChatId"
            @select-chat="handleChatSelect"
          />
        </div>
        
        <div class="chat-main">
          <template v-if="selectedChatId">
            <ChatInterface
              :isOpen="true"
              :currentUserId="currentUserId"
              :messages="currentChatMessages"
              :adminAvatar="adminAvatar"
              :isAdminOnline="true"
              @close="handleClose"
              @send-message="handleSendMessage"
            />
          </template>
          <div v-else class="no-chat-selected">
            <i class="fas fa-comments"></i>
            <h2>Select a conversation</h2>
            <p>Choose a conversation from the list to start chatting</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Customer Chat -->
    <template v-else>
      <FloatingChatButton 
        :unreadCount="unreadCount"
        :isOpen="isChatOpen"
        @toggle-chat="toggleChat"
      />
      
      <ChatInterface
        v-if="isChatOpen"
        :isOpen="true"
        :currentUserId="currentUserId"
        :messages="messages"
        :adminAvatar="adminAvatar"
        :isAdminOnline="isAdminOnline"
        @close="toggleChat"
        @send-message="handleSendMessage"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminChatList from '@/components/admin/AdminChatList.vue';
import ChatInterface from '@/components/chat/ChatInterface.vue';
import FloatingChatButton from '@/components/chat/FloatingChatButton.vue';
import axios from 'axios';

const router = useRouter();
const isAdmin = computed(() => {
  const role = localStorage.getItem('user_role');
  return role === 'admin' || role === 'staff';
});

const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

// Admin state
const chats = ref([]);
const selectedChatId = ref(null);
const adminAvatar = '/src/assets/admin-avatar.png';
const isAdminOnline = ref(true);

// Customer state
const isChatOpen = ref(false);
const messages = ref([]);
const unreadCount = ref(0);

// Computed for current chat messages
const currentChatMessages = computed(() => {
  if (!selectedChatId.value) return [];
  const chat = chats.value.find(c => c.id === selectedChatId.value);
  return chat?.messages || [];
});

// Methods
const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value;
};

const handleClose = () => {
  if (isAdmin.value) {
    selectedChatId.value = null;
  } else {
    isChatOpen.value = false;
  }
};

const handleChatSelect = (chat) => {
  selectedChatId.value = chat.id;
  // Mark messages as read
  markMessagesAsRead(chat.id);
};

const handleSendMessage = async (message) => {
  try {
    const token = localStorage.getItem('token');
    const chatId = isAdmin.value ? selectedChatId.value : null;
    
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/messages`, 
      {
        content: message.content,
        chat_id: chatId,
        recipient_id: isAdmin.value ? null : 1 // Send to admin
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );

    // Add message to the list
    if (isAdmin.value) {
      const chatIndex = chats.value.findIndex(c => c.id === selectedChatId.value);
      if (chatIndex !== -1) {
        chats.value[chatIndex].messages.push(response.data);
      }
    } else {
      messages.value.push(response.data);
    }
  } catch (error) {
    console.error('Error sending message:', error);
    // Handle error (show notification, etc.)
  }
};

const markMessagesAsRead = async (chatId) => {
  try {
    const token = localStorage.getItem('token');
    await axios.post(
      `${import.meta.env.VITE_API_URL}/api/messages/mark-read`,
      { chat_id: chatId },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    
    // Update local state
    const chatIndex = chats.value.findIndex(c => c.id === chatId);
    if (chatIndex !== -1) {
      chats.value[chatIndex].unread_count = 0;
    }
  } catch (error) {
    console.error('Error marking messages as read:', error);
  }
};

// Fetch data methods
const fetchChats = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${import.meta.env.VITE_API_URL}/api/chats`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    chats.value = response.data;
  } catch (error) {
    console.error('Error fetching chats:', error);
  }
};

const fetchMessages = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${import.meta.env.VITE_API_URL}/api/messages`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    messages.value = response.data;
  } catch (error) {
    console.error('Error fetching messages:', error);
  }
};

const fetchUnreadCount = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${import.meta.env.VITE_API_URL}/api/messages/unread-count`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    unreadCount.value = response.data.count;
  } catch (error) {
    console.error('Error fetching unread count:', error);
  }
};

// Polling for updates
let pollInterval;

const startPolling = () => {
  if (isAdmin.value) {
    fetchChats();
  } else {
    fetchMessages();
    fetchUnreadCount();
  }
  
  // Poll every 10 seconds
  pollInterval = setInterval(() => {
    if (isAdmin.value) {
      fetchChats();
    } else {
      fetchMessages();
      fetchUnreadCount();
    }
  }, 10000);
};

onMounted(() => {
  startPolling();
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});
</script>

<style scoped>
.chat-view {
  height: 100%;
  position: relative;
}

.chat-container {
  display: flex;
  height: calc(100vh - 80px); /* Subtract header height */
  background: white;
  position: relative;
  overflow: hidden;
}

.chat-sidebar {
  width: 380px;
  border-right: 1px solid #E5E7EB;
  height: 100%;
  overflow-y: auto;
  background: white;
  position: relative;
  z-index: 1;
}

.chat-main {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #F9FAFB;
  position: relative;
  overflow: hidden;
}

.chat-interface-container {
  position: fixed;
  bottom: 0;
  right: 0;
  z-index: 1000;
}

.no-chat-selected {
  text-align: center;
  color: #6B7280;
}

.no-chat-selected i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.no-chat-selected h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  color: #111827;
}

.no-chat-selected p {
  margin: 0;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .chat-container {
    flex-direction: column;
  }

  .chat-sidebar {
    width: 100%;
    height: 50%;
  }

  .chat-main {
    height: 50%;
  }
}
</style> 