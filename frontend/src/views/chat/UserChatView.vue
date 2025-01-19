<template>
  <div class="chat-view">
    <div class="chat-container">
      <div class="chat-header">
        <h2>Chat with Support</h2>
      </div>
      
      <div class="chat-content">
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading chat...</p>
        </div>
        
        <div v-else-if="!currentChat" class="start-chat">
          <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h3>Start a Conversation</h3>
            <p>Our support team is here to help you</p>
            <button @click="startNewChat" class="start-chat-btn">
              Start New Chat
            </button>
          </div>
        </div>
        
        <ChatWindow 
          v-else
          :chat-id="currentChat._id"
          :current-user-id="currentUserId"
          :is-admin="false"
          @close="handleClose"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import ChatWindow from '@/components/chat/ChatWindow.vue';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const currentChat = ref(null);

const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

const startNewChat = async () => {
  try {
    loading.value = true;
    const response = await axios.post('/api/chat', {
      type: 'support',
      message: 'Hello, I need assistance.'
    });
    currentChat.value = response.data.chat;
  } catch (error) {
    console.error('Error starting chat:', error);
  } finally {
    loading.value = false;
  }
};

const handleClose = () => {
  currentChat.value = null;
  router.push('/');
};

onMounted(async () => {
  if (route.query.userId) {
    try {
      loading.value = true;
      const response = await axios.get(`/api/chat/active/${route.query.userId}`);
      if (response.data.chat) {
        currentChat.value = response.data.chat;
      }
    } catch (error) {
      console.error('Error fetching active chat:', error);
    } finally {
      loading.value = false;
    }
  }
});
</script>

<style scoped>
.chat-view {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  min-height: calc(100vh - 64px);
}

.chat-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1rem 1.5rem;
  background: var(--primary, #7380ec);
  color: white;
}

.chat-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 500;
}

.chat-content {
  flex: 1;
  overflow: hidden;
  position: relative;
}

.loading-state,
.start-chat {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.empty-state {
  text-align: center;
  padding: 2rem;
}

.empty-state i {
  font-size: 3rem;
  color: var(--primary, #7380ec);
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin: 0.5rem 0;
  color: #374151;
}

.empty-state p {
  color: #6B7280;
  margin-bottom: 1.5rem;
}

.start-chat-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary, #7380ec);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.start-chat-btn:hover {
  background: #6470db;
}

@media (max-width: 768px) {
  .chat-view {
    padding: 1rem;
  }
  
  .chat-container {
    height: calc(100vh - 80px);
  }
}
</style> 