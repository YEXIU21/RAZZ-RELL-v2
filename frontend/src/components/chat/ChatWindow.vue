<template>
  <div class="chat-window" :class="{ 'is-minimized': isMinimized }">
    <div class="chat-header" @click="toggleMinimize">
      <div class="chat-title">
        <span v-if="agent">
          Chat with {{ agent.name }}
          <span class="status-dot" :class="{ online: agentIsOnline }"></span>
        </span>
        <span v-else>Customer Support</span>
      </div>
      <div class="chat-actions">
        <button v-if="!isMinimized" @click.stop="minimize" class="action-btn">
          <i class="fas fa-minus"></i>
        </button>
        <button @click.stop="close" class="action-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <div v-if="!isMinimized" class="chat-body">
      <div class="messages" ref="messagesContainer">
        <div v-if="loading" class="loading-messages">
          <LoadingAnimation />
        </div>
        
        <template v-else>
          <div v-for="message in messages" :key="message._id" 
               class="message" :class="{ 'message-own': isOwnMessage(message) }">
            <div class="message-content">
              {{ message.content }}
              <div v-if="message.attachment" class="attachment">
                <a :href="message.attachment.path" target="_blank">
                  {{ message.attachment.filename }}
                </a>
              </div>
            </div>
            <div class="message-meta">
              {{ formatTime(message.timestamp) }}
              <i v-if="isOwnMessage(message)" 
                 class="fas fa-check" 
                 :class="{ 'read': message.read }">
              </i>
            </div>
          </div>

          <div v-if="typingUsers.length" class="typing-indicator">
            {{ typingUsers.join(', ') }} {{ typingUsers.length > 1 ? 'are' : 'is' }} typing...
          </div>
        </template>
      </div>

      <div class="chat-input">
        <div class="attachment-preview" v-if="selectedFile">
          <span>{{ selectedFile.name }}</span>
          <button @click="removeAttachment" class="remove-attachment">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="input-container">
          <textarea
            v-model="messageText"
            @keydown.enter.prevent="sendMessage"
            @input="handleTyping"
            placeholder="Type a message..."
            :disabled="!canSendMessage"
          ></textarea>

          <div class="input-actions">
            <button @click="triggerFileInput" class="action-btn">
              <i class="fas fa-paperclip"></i>
            </button>
            <input
              type="file"
              ref="fileInput"
              @change="handleFileSelect"
              style="display: none"
            >
            <button 
              @click="sendMessage" 
              :disabled="!canSendMessage || !messageText.trim()"
              class="send-btn"
            >
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useSocket } from '@/composables/useSocket';
import { useAuth } from '@/composables/useAuth';
import { useNotifications } from '@/composables/useNotifications';
import LoadingAnimation from '@/components/ui/LoadingAnimation.vue';
import moment from 'moment';
import axios from 'axios';

const props = defineProps({
  chatId: {
    type: String,
    required: true
  },
  currentUserId: {
    type: [Number, String],
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

const { user } = useAuth();
const { showNotification } = useNotifications();
const socket = useSocket();

const messages = ref([]);
const messageText = ref('');
const loading = ref(true);
const isMinimized = ref(false);
const typingUsers = ref([]);
const typingTimeout = ref(null);
const selectedFile = ref(null);
const fileInput = ref(null);
const messagesContainer = ref(null);
const agentIsOnline = ref(false);

const canSendMessage = computed(() => {
  return messageText.value.trim() && !loading.value;
});

onMounted(async () => {
  await initializeChat();
  setupSocketListeners();
  scrollToBottom();
});

onUnmounted(() => {
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
  socket.leaveChat(props.chatId);
});

watch(messages, () => {
  nextTick(() => {
    scrollToBottom();
  });
}, { deep: true });

const initializeChat = async () => {
  try {
    loading.value = true;
    // Fetch chat history
    const response = await axios.get(`/api/chat/${props.chatId}/messages`);
    messages.value = response.data.messages;
    
    // Join socket room
    socket.joinChat(props.chatId);
    
    loading.value = false;
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error initializing chat:', error);
    showNotification('Error loading chat history', 'error');
    loading.value = false;
  }
};

const setupSocketListeners = () => {
  socket.onNewMessage((data) => {
    if (data.chatId === props.chatId) {
      messages.value.push(data.message);
    }
  });

  socket.onTypingStatus((data) => {
    if (data.chatId === props.chatId) {
      typingUsers.value = data.typingUsers.filter(id => id !== props.currentUserId);
    }
  });
};

const sendMessage = async () => {
  if (!canSendMessage.value) return;
  
  try {
    const response = await axios.post(`/api/chat/${props.chatId}/messages`, {
      content: messageText.value
    });
    
    messages.value.push(response.data.message);
    messageText.value = '';
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error sending message:', error);
    showNotification('Error sending message', 'error');
  }
};

const handleTyping = () => {
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
  
  socket.emitTyping(props.chatId, true);
  
  typingTimeout.value = setTimeout(() => {
    socket.emitTyping(props.chatId, false);
  }, 1000);
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB limit
      showNotification('File size must be less than 5MB', 'error');
      return;
    }
    selectedFile.value = file;
  }
};

const uploadAttachment = async () => {
  const formData = new FormData();
  formData.append('file', selectedFile.value);

  try {
    const response = await fetch(`/api/chat/${props.chatId}/upload`, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error('Upload failed');
    }

    const data = await response.json();
    return data.path;
  } catch (error) {
    showNotification('Error uploading file', 'error');
    throw error;
  }
};

const removeAttachment = () => {
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const isOwnMessage = (message) => {
  return message.sender._id === user.value._id;
};

const formatTime = (timestamp) => {
  return moment(timestamp).format('HH:mm');
};

const toggleMinimize = () => {
  isMinimized.value = !isMinimized.value;
};

const minimize = () => {
  isMinimized.value = true;
};

const close = () => {
  emit('close');
};
</script>

<style scoped>
.chat-window {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 350px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  overflow: hidden;
}

.chat-window.is-minimized {
  height: auto;
}

.chat-header {
  background: #4F46E5;
  color: white;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
}

.chat-title {
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9CA3AF;
}

.status-dot.online {
  background: #10B981;
}

.chat-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 4px;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.action-btn:hover {
  opacity: 1;
}

.chat-body {
  height: 400px;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message {
  max-width: 80%;
  align-self: flex-start;
}

.message-own {
  align-self: flex-end;
}

.message-content {
  background: #F3F4F6;
  padding: 8px 12px;
  border-radius: 12px;
  border-bottom-left-radius: 4px;
  font-size: 0.9rem;
  line-height: 1.4;
}

.message-own .message-content {
  background: #4F46E5;
  color: white;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 4px;
}

.message-meta {
  font-size: 0.75rem;
  color: #6B7280;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.typing-indicator {
  font-size: 0.8rem;
  color: #6B7280;
  font-style: italic;
  padding: 4px 0;
}

.chat-input {
  padding: 12px;
  border-top: 1px solid #E5E7EB;
}

.attachment-preview {
  background: #F3F4F6;
  padding: 8px 12px;
  border-radius: 8px;
  margin-bottom: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}

.remove-attachment {
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
}

.input-container {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

textarea {
  flex: 1;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 8px 12px;
  resize: none;
  height: 40px;
  max-height: 120px;
  font-size: 0.9rem;
  line-height: 1.4;
}

.input-actions {
  display: flex;
  gap: 8px;
}

.send-btn {
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.send-btn:hover {
  background: #4338CA;
}

.send-btn:disabled {
  background: #9CA3AF;
  cursor: not-allowed;
}

.loading-messages {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.attachment {
  margin-top: 8px;
  font-size: 0.85rem;
}

.attachment a {
  color: inherit;
  text-decoration: underline;
}

.fa-check.read {
  color: #10B981;
}
</style> 