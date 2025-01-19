<template>
  <div class="chat-container">
    <div class="chat-sidebar" :class="{ 'mobile-hidden': isMobileView && selectedChat }">
      <ChatList
        :selectedChatId="selectedChat?._id"
        @select-chat="handleChatSelect"
      />
    </div>

    <div class="chat-main" :class="{ 'mobile-hidden': isMobileView && !selectedChat }">
      <div v-if="selectedChat" class="chat-content">
        <ChatWindow
          :chatId="selectedChat._id"
          :agent="selectedChat.agent"
          @close="handleClose"
        />
      </div>
      <div v-else class="no-chat-selected">
        <div class="empty-state">
          <i class="fas fa-comments"></i>
          <h3>Select a chat to start messaging</h3>
          <p>Choose from your active conversations on the left</p>
        </div>
      </div>
    </div>

    <button 
      v-if="isMobileView" 
      class="mobile-back-btn"
      @click="handleBack"
    >
      <i class="fas fa-arrow-left"></i>
      Back
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ChatList from './ChatList.vue';
import ChatWindow from './ChatWindow.vue';
import moment from 'moment';

const selectedChat = ref(null);
const isMobileView = ref(false);

const handleChatSelect = (chat) => {
  if (!chat) return;
  selectedChat.value = chat;
};

const handleClose = () => {
  selectedChat.value = null;
};

const handleBack = () => {
  selectedChat.value = null;
};

const checkMobileView = () => {
  isMobileView.value = window.innerWidth < 768;
};

onMounted(() => {
  checkMobileView();
  window.addEventListener('resize', checkMobileView);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobileView);
});
</script>

<style scoped>
.chat-container {
  display: flex;
  height: 100%;
  position: relative;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.chat-sidebar {
  width: 350px;
  border-right: 1px solid #E5E7EB;
  height: 100%;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-content {
  flex: 1;
  height: 100%;
}

.no-chat-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  background: #F9FAFB;
}

.empty-state {
  text-align: center;
  color: #6B7280;
  padding: 2rem;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #9CA3AF;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6B7280;
}

.mobile-back-btn {
  display: none;
  position: fixed;
  bottom: 20px;
  left: 20px;
  padding: 12px 24px;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 9999px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  z-index: 50;
}

.mobile-back-btn i {
  margin-right: 8px;
}

@media (max-width: 768px) {
  .chat-container {
    flex-direction: column;
  }

  .chat-sidebar,
  .chat-main {
    width: 100%;
  }

  .mobile-hidden {
    display: none;
  }

  .mobile-back-btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .chat-sidebar {
    height: 100vh;
  }

  .chat-main {
    height: 100vh;
  }
}
</style> 