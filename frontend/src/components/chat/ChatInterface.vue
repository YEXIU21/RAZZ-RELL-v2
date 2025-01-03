<template>
  <div class="chat-interface" :class="{ 'is-open': isOpen }">
    <div class="chat-header">
      <div class="header-info">
        <div class="admin-avatar">
          <img :src="adminAvatar" alt="Admin" />
          <span class="status-dot" :class="{ 'online': isAdminOnline }"></span>
        </div>
        <div class="admin-info">
          <h3>Customer Support</h3>
          <p>{{ isAdminOnline ? 'Online' : 'Offline' }}</p>
        </div>
      </div>
      <button class="close-btn" @click="$emit('close')">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="chat-messages" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <i class="fas fa-comments"></i>
        <p>Start a conversation with our support team</p>
      </div>
      
      <template v-else>
        <div 
          v-for="message in messages" 
          :key="message.id" 
          class="message"
          :class="{ 
            'outgoing': message.sender_id === currentUserId,
            'incoming': message.sender_id !== currentUserId 
          }"
        >
          <div class="message-content">
            <p>{{ message.content }}</p>
            <span class="message-time">{{ formatTime(message.created_at) }}</span>
          </div>
        </div>
      </template>
    </div>

    <div class="chat-input">
      <textarea 
        v-model="newMessage" 
        placeholder="Type your message..."
        @keydown.enter.prevent="sendMessage"
        rows="1"
        ref="messageInput"
      ></textarea>
      <button 
        class="send-btn" 
        :disabled="!newMessage.trim()"
        @click="sendMessage"
      >
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { format } from 'date-fns';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  currentUserId: {
    type: [Number, String],
    required: true
  },
  adminAvatar: {
    type: String,
    default: '/src/assets/default-avatar.png'
  },
  isAdminOnline: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'send-message']);

const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);
const messageInput = ref(null);

const formatTime = (timestamp) => {
  return format(new Date(timestamp), 'h:mm a');
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = () => {
  if (!newMessage.value.trim()) return;
  
  emit('send-message', {
    content: newMessage.value,
    sender_id: props.currentUserId,
    created_at: new Date().toISOString()
  });
  
  newMessage.value = '';
  scrollToBottom();
};

// Auto-resize textarea
const resizeTextarea = () => {
  const textarea = messageInput.value;
  if (!textarea) return;
  
  textarea.style.height = 'auto';
  textarea.style.height = textarea.scrollHeight + 'px';
};

watch(() => newMessage.value, resizeTextarea);

watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      messageInput.value?.focus();
      scrollToBottom();
    });
  }
});

onMounted(() => {
  scrollToBottom();
});
</script>

<style scoped>
.chat-interface {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 380px;
  height: 600px;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  transform: translateY(100vh);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 9998;
  opacity: 0;
  pointer-events: none;
}

.chat-interface.is-open {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}

.chat-header {
  padding: 1rem;
  background: #4F46E5;
  color: white;
  border-radius: 1rem 1rem 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.admin-avatar {
  position: relative;
}

.admin-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid white;
  object-fit: cover;
}

.status-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #9CA3AF;
  border: 2px solid white;
}

.status-dot.online {
  background: #10B981;
}

.admin-info h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.admin-info p {
  margin: 0;
  font-size: 0.875rem;
  opacity: 0.8;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
  font-size: 1.25rem;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.close-btn:hover {
  opacity: 1;
}

.chat-messages {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #9CA3AF;
  gap: 1rem;
}

.empty-state i {
  font-size: 3rem;
}

.empty-state p {
  margin: 0;
  font-size: 0.875rem;
}

.message {
  display: flex;
  margin-bottom: 0.5rem;
}

.message.outgoing {
  justify-content: flex-end;
}

.message-content {
  max-width: 80%;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  position: relative;
}

.message.incoming .message-content {
  background: #F3F4F6;
  border-bottom-left-radius: 0.25rem;
}

.message.outgoing .message-content {
  background: #4F46E5;
  color: white;
  border-bottom-right-radius: 0.25rem;
}

.message-content p {
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.4;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.8;
  margin-top: 0.25rem;
  display: block;
}

.chat-input {
  padding: 1rem;
  border-top: 1px solid #E5E7EB;
  display: flex;
  gap: 0.75rem;
  align-items: flex-end;
}

textarea {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  resize: none;
  max-height: 120px;
  font-size: 0.875rem;
  line-height: 1.4;
  outline: none;
  transition: border-color 0.2s;
  background-color: white;
  color: #000000;
}

textarea:focus {
  border-color: #4F46E5;
}

.send-btn {
  padding: 0.75rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.send-btn:hover:not(:disabled) {
  background: #4338CA;
}

.send-btn:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .chat-interface {
    bottom: 0;
    right: 0;
    width: 100%;
    height: 100vh;
    border-radius: 0;
    transform: translateX(100%);
  }

  .chat-interface.is-open {
    transform: translateX(0);
  }

  .chat-header {
    border-radius: 0;
    padding: 1rem;
  }

  .chat-messages {
    padding: 1rem;
  }

  .chat-input {
    padding: 1rem;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
  }
}

@supports (padding: max(0px)) {
  .chat-interface {
    padding-bottom: env(safe-area-inset-bottom);
  }
}
</style> 