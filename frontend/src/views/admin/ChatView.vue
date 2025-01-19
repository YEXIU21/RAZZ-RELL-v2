<template>
  <div class="admin-chat-view">
    <div class="chat-sidebar">
      <AdminChatList
        :selected-chat-id="selectedChatId"
        @select-chat="handleChatSelect"
        @transfer-chat="handleChatTransfer"
        @close-chat="handleChatClose"
      />
    </div>

    <div class="chat-main">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading chat...</p>
      </div>

      <div v-else-if="!selectedChatId" class="empty-state">
        <i class="fas fa-comments"></i>
        <h2>Select a Chat</h2>
        <p>Choose a conversation from the list to start chatting</p>
      </div>

      <div v-else class="chat-content">
        <div class="chat-header">
          <div class="user-info">
            <img :src="currentChat?.user?.avatar || defaultAvatar" :alt="currentChat?.user?.name" class="user-avatar">
            <div class="user-details">
              <h3>{{ currentChat?.user?.name }}</h3>
              <p class="user-email">{{ currentChat?.user?.email }}</p>
            </div>
          </div>

          <div class="chat-actions">
            <button 
              v-if="currentChat?.status !== 'closed'"
              @click="showTransferModal = true"
              class="action-btn"
              title="Transfer Chat"
            >
              <i class="fas fa-exchange-alt"></i>
              Transfer
            </button>
            <button 
              v-if="currentChat?.status !== 'closed'"
              @click="showCloseModal = true"
              class="action-btn danger"
              title="Close Chat"
            >
              <i class="fas fa-times"></i>
              Close
            </button>
          </div>
        </div>

        <div class="messages-container" ref="messagesContainer">
          <div v-if="messages.length === 0" class="no-messages">
            <p>No messages yet</p>
          </div>

          <template v-else>
            <div 
              v-for="message in messages" 
              :key="message.id" 
              class="message"
              :class="{ 'outgoing': message.sender_id === currentUserId }"
            >
              <div class="message-content">
                <p>{{ message.content }}</p>
                <span class="message-time">{{ formatTime(message.created_at) }}</span>
              </div>
            </div>
          </template>
        </div>

        <div v-if="currentChat?.status !== 'closed'" class="chat-input">
          <textarea
            v-model="newMessage"
            placeholder="Type your message..."
            @keydown.enter.prevent="sendMessage"
            :disabled="sending"
          ></textarea>
          <button 
            @click="sendMessage"
            :disabled="!newMessage.trim() || sending"
            class="send-btn"
          >
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>

        <div v-else class="chat-closed">
          <p>This chat has been closed</p>
        </div>
      </div>
    </div>

    <!-- Transfer Chat Modal -->
    <div v-if="showTransferModal" class="modal-container">
      <div class="modal-backdrop" @click="showTransferModal = false" />
      <div class="modal-content">
        <h3 class="modal-title">Transfer Chat</h3>

        <div class="modal-body">
          <div class="form-group">
            <label for="staff">Select Staff Member</label>
            <select 
              id="staff" 
              v-model="selectedStaff"
              class="form-select"
            >
              <option value="">Select a staff member</option>
              <option 
                v-for="staff in staffMembers" 
                :key="staff.id" 
                :value="staff.id"
              >
                {{ staff.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="transferNote">Transfer Note (Optional)</label>
            <textarea
              id="transferNote"
              v-model="transferNote"
              placeholder="Add a note for the staff member..."
              class="form-textarea"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            @click="showTransferModal = false"
            class="btn btn-secondary"
          >
            Cancel
          </button>
          <button 
            @click="handleTransfer"
            class="btn btn-primary"
            :disabled="!selectedStaff"
          >
            Transfer Chat
          </button>
        </div>
      </div>
    </div>

    <!-- Close Chat Modal -->
    <div v-if="showCloseModal" class="modal-container">
      <div class="modal-backdrop" @click="showCloseModal = false" />
      <div class="modal-content">
        <h3 class="modal-title">Close Chat</h3>

        <div class="modal-body">
          <p>Are you sure you want to close this chat? This action cannot be undone.</p>
          <div class="form-group">
            <label for="closeNote">Closing Note (Optional)</label>
            <textarea
              id="closeNote"
              v-model="closeNote"
              placeholder="Add a closing note..."
              class="form-textarea"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            @click="showCloseModal = false"
            class="btn btn-secondary"
          >
            Cancel
          </button>
          <button 
            @click="handleClose"
            class="btn btn-danger"
          >
            Close Chat
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import AdminChatList from '@/components/admin/chat/AdminChatList.vue';
import defaultAvatar from '@/assets/default-profile.png';
import { formatDistanceToNow } from 'date-fns';
import { io } from 'socket.io-client';

const loading = ref(false);
const selectedChatId = ref(null);
const currentChat = ref(null);
const messages = ref([]);
const newMessage = ref('');
const sending = ref(false);
const messagesContainer = ref(null);
const socket = ref(null);

// Modal state
const showTransferModal = ref(false);
const showCloseModal = ref(false);
const selectedStaff = ref('');
const transferNote = ref('');
const closeNote = ref('');
const staffMembers = ref([]);

const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

const formatTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const handleChatSelect = async (chat) => {
  selectedChatId.value = chat.id;
  currentChat.value = chat;
  await fetchMessages(chat.id);
  scrollToBottom();
};

const handleChatTransfer = (data) => {
  // This will be handled by the AdminChatList component
  console.log('Chat transferred:', data);
};

const handleChatClose = (data) => {
  // This will be handled by the AdminChatList component
  console.log('Chat closed:', data);
};

const fetchMessages = async (chatId) => {
  try {
    loading.value = true;
    const response = await fetch(`/api/admin/chats/${chatId}/messages`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      messages.value = data.messages;
    }
  } catch (error) {
    console.error('Error fetching messages:', error);
  } finally {
    loading.value = false;
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return;
  
  try {
    sending.value = true;
    const response = await fetch(`/api/admin/chats/${selectedChatId.value}/messages`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        content: newMessage.value
      })
    });

    if (response.ok) {
      const data = await response.json();
      messages.value.push(data.message);
      newMessage.value = '';
      scrollToBottom();
    }
  } catch (error) {
    console.error('Error sending message:', error);
  } finally {
    sending.value = false;
  }
};

onMounted(() => {
  // Initialize socket connection
  socket.value = io(import.meta.env.VITE_SOCKET_URL || 'http://localhost:3001', {
    auth: {
      token: localStorage.getItem('auth_token')
    }
  });

  // Listen for new messages
  socket.value.on('receive_message', (message) => {
    if (message.chat_id === selectedChatId.value) {
      messages.value.push(message);
      scrollToBottom();
    }
  });

  // Listen for chat updates
  socket.value.on('chat_updated', (chat) => {
    if (chat.id === selectedChatId.value) {
      currentChat.value = chat;
    }
  });
});

onUnmounted(() => {
  if (socket.value) {
    socket.value.disconnect();
  }
});
</script>

<style scoped>
.admin-chat-view {
  display: flex;
  height: calc(100vh - 64px);
  background: white;
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

.loading-state,
.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  text-align: center;
  padding: 2rem;
}

.loading-state i,
.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #4F46E5;
}

.empty-state h2 {
  margin: 0 0 0.5rem;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1F2937;
}

.empty-state p {
  margin: 0;
  color: #6B7280;
}

.chat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.chat-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1F2937;
}

.user-email {
  margin: 0;
  font-size: 0.875rem;
  color: #6B7280;
}

.chat-actions {
  display: flex;
  gap: 0.75rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.action-btn i {
  font-size: 1rem;
}

.action-btn {
  background: #F3F4F6;
  color: #374151;
}

.action-btn:hover {
  background: #E5E7EB;
}

.action-btn.danger {
  background: #FEE2E2;
  color: #991B1B;
}

.action-btn.danger:hover {
  background: #FEE2E2;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.no-messages {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
}

.message {
  display: flex;
  margin-bottom: 0.5rem;
}

.message.outgoing {
  justify-content: flex-end;
}

.message-content {
  max-width: 70%;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  position: relative;
}

.message:not(.outgoing) .message-content {
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
  padding: 1rem 1.5rem;
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
  height: 40px;
  max-height: 120px;
  font-family: inherit;
  font-size: 0.875rem;
  line-height: 1.4;
}

.send-btn {
  padding: 0.75rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.send-btn:hover:not(:disabled) {
  background: #4338CA;
}

.send-btn:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

.chat-closed {
  padding: 1rem 1.5rem;
  background: #F3F4F6;
  color: #6B7280;
  text-align: center;
  border-top: 1px solid #E5E7EB;
}

/* Modal Styles */
.modal-container {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  z-index: 1;
}

.modal-title {
  margin: 0 0 1.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1F2937;
}

.modal-body {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1F2937;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary {
  background: #F3F4F6;
  color: #374151;
}

.btn-secondary:hover {
  background: #E5E7EB;
}

.btn-primary {
  background: #4F46E5;
  color: white;
}

.btn-primary:hover {
  background: #4338CA;
}

.btn-primary:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

.btn-danger {
  background: #DC2626;
  color: white;
}

.btn-danger:hover {
  background: #B91C1C;
}

@media (max-width: 768px) {
  .admin-chat-view {
    flex-direction: column;
  }

  .chat-sidebar {
    width: 100%;
    height: auto;
    max-height: 40vh;
  }

  .chat-main {
    height: 60vh;
  }

  .chat-header {
    padding: 0.75rem 1rem;
  }

  .messages-container {
    padding: 1rem;
  }

  .chat-input {
    padding: 0.75rem 1rem;
  }
}
</style> 