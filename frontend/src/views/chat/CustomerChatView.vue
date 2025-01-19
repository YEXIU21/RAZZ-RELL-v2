<template>
  <div class="chat-container">
    <!-- Contact List Sidebar -->
    <div class="contacts-sidebar">
      <div class="sidebar-header">
        <h2>Messages</h2>
      </div>
      
      <div class="contacts-list">
        <div 
          v-for="contact in adminStaffContacts" 
          :key="contact.id"
          class="contact-item"
          :class="{ active: selectedContact?.id === contact.id }"
          @click="selectContact(contact)"
        >
          <div class="contact-avatar">
            <img :src="contact.avatar || '/default-avatar.png'" :alt="contact.name">
            <span class="status-dot" :class="{ online: contact.isOnline }"></span>
          </div>
          <div class="contact-info">
            <div class="contact-header">
              <span class="contact-name">{{ contact.name }}</span>
              <span class="last-time">{{ formatTime(contact.lastMessage?.timestamp) }}</span>
            </div>
            <div class="last-message" :class="{ unread: !contact.lastMessage?.read }">
              <span class="message-preview">{{ contact.lastMessage?.content || 'No messages yet' }}</span>
              <span v-if="contact.unreadCount" class="unread-count">{{ contact.unreadCount }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
      <template v-if="selectedContact">
        <div class="chat-header">
          <div class="contact-info">
            <div class="contact-avatar">
              <img :src="selectedContact.avatar || '/default-avatar.png'" :alt="selectedContact.name">
              <span class="status-dot" :class="{ online: selectedContact.isOnline }"></span>
            </div>
            <div class="contact-details">
              <h3>{{ selectedContact.name }}</h3>
              <span class="status-text">{{ selectedContact.isOnline ? 'Active Now' : 'Offline' }}</span>
            </div>
          </div>
        </div>

        <div class="messages-container" ref="messagesContainer">
          <div v-if="loading" class="loading-messages">
            <i class="fas fa-spinner fa-spin"></i>
            Loading messages...
          </div>

          <template v-else>
            <div 
              v-for="message in messages" 
              :key="message.id"
              class="message"
              :class="{ 
                'own-message': message.senderId === currentUserId,
                'other-message': message.senderId !== currentUserId
              }"
            >
              <div class="message-content">
                <p>{{ message.content }}</p>
                <span class="message-time">{{ formatTime(message.timestamp) }}</span>
                <span v-if="message.senderId === currentUserId" class="message-status">
                  <i class="fas" :class="message.read ? 'fa-check-double' : 'fa-check'"></i>
                </span>
              </div>
            </div>

            <div v-if="messages.length === 0" class="no-messages">
              <p>No messages yet. Start a conversation!</p>
            </div>
          </template>
        </div>

        <div class="chat-input">
          <div class="input-wrapper">
            <textarea 
              v-model="newMessage" 
              placeholder="Type a message..." 
              @keydown.enter.prevent="sendMessage"
              ref="messageInput"
              rows="1"
              @input="autoGrow"
            ></textarea>

            <div class="input-actions">
              <button class="attach-btn" @click="triggerFileInput">
                <i class="fas fa-paperclip"></i>
              </button>
              <button class="send-btn" :disabled="!canSendMessage" @click="sendMessage">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>

            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileSelect" 
              accept="image/*"
              style="display: none"
            >
          </div>

          <div v-if="selectedFile" class="selected-file">
            <img :src="filePreview" alt="Selected file preview" class="file-preview">
            <button class="remove-file" @click="removeSelectedFile">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </template>

      <div v-else class="no-chat-selected">
        <div class="placeholder">
          <i class="fas fa-comments"></i>
          <h3>Select a conversation</h3>
          <p>Choose from your existing conversations with admin or staff</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props & Emits
const props = defineProps({
  currentUserId: {
    type: [Number, String],
    required: true
  }
});

// State
const adminStaffContacts = ref([]);
const selectedContact = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(false);
const messagesContainer = ref(null);
const messageInput = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);
const filePreview = ref('');

// Computed
const canSendMessage = computed(() => {
  return newMessage.value.trim() || selectedFile.value;
});

// Methods
const fetchAdminStaffContacts = async () => {
  try {
    const response = await axios.get('/api/chat/contacts/admin-staff');
    adminStaffContacts.value = response.data.contacts;
  } catch (error) {
    console.error('Error fetching contacts:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load contacts'
    });
  }
};

const selectContact = async (contact) => {
  selectedContact.value = contact;
  loading.value = true;
  try {
    const response = await axios.get(`/api/chat/messages/${contact.id}`);
    messages.value = response.data.messages;
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error loading messages:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load messages'
    });
  } finally {
    loading.value = false;
  }
};

const sendMessage = async () => {
  if (!canSendMessage.value) return;

  try {
    const formData = new FormData();
    formData.append('receiver_id', selectedContact.value.id);
    formData.append('content', newMessage.value);
    
    if (selectedFile.value) {
      formData.append('attachment', selectedFile.value);
    }

    const response = await axios.post('/api/chat/send', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    messages.value.push(response.data.message);
    newMessage.value = '';
    removeSelectedFile();
    await nextTick();
    scrollToBottom();

  } catch (error) {
    console.error('Error sending message:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to send message'
    });
  }
};

const autoGrow = () => {
  const textarea = messageInput.value;
  textarea.style.height = 'auto';
  textarea.style.height = textarea.scrollHeight + 'px';
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const formatTime = (timestamp) => {
  if (!timestamp) return '';
  
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  const oneDay = 24 * 60 * 60 * 1000;

  if (diff < oneDay) {
    return date.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    });
  } else if (diff < 7 * oneDay) {
    return date.toLocaleDateString('en-US', { weekday: 'short' });
  } else {
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric'
    });
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid File',
      text: 'Please select an image file'
    });
    return;
  }

  if (file.size > 5 * 1024 * 1024) { // 5MB limit
    Swal.fire({
      icon: 'error',
      title: 'File Too Large',
      text: 'File size should be less than 5MB'
    });
    return;
  }

  selectedFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    filePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeSelectedFile = () => {
  selectedFile.value = null;
  filePreview.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Lifecycle
onMounted(() => {
  fetchAdminStaffContacts();
});

// Watch for new messages to mark them as read
watch(messages, async (newMessages) => {
  if (selectedContact.value) {
    const unreadMessages = newMessages.filter(m => 
      !m.read && m.senderId !== props.currentUserId
    );

    if (unreadMessages.length > 0) {
      try {
        await axios.post('/api/chat/mark-read', {
          contact_id: selectedContact.value.id,
          message_ids: unreadMessages.map(m => m.id)
        });
      } catch (error) {
        console.error('Error marking messages as read:', error);
      }
    }
  }
}, { deep: true });
</script>

<style scoped>
.chat-container {
  display: flex;
  height: 100vh;
  background: white;
}

.contacts-sidebar {
  width: 320px;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.sidebar-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.contacts-list {
  flex: 1;
  overflow-y: auto;
}

.contact-item {
  display: flex;
  padding: 0.75rem 1rem;
  gap: 0.75rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.contact-item:hover {
  background-color: #f9fafb;
}

.contact-item.active {
  background-color: #f3f4f6;
}

.contact-avatar {
  position: relative;
}

.contact-avatar img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.status-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #9ca3af;
  border: 2px solid white;
}

.status-dot.online {
  background: #10b981;
}

.contact-info {
  flex: 1;
  min-width: 0;
}

.contact-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 0.25rem;
}

.contact-name {
  font-weight: 500;
  color: #111827;
}

.last-time {
  font-size: 0.75rem;
  color: #6b7280;
}

.last-message {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #6b7280;
  font-size: 0.875rem;
}

.message-preview {
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread .message-preview {
  color: #111827;
  font-weight: 500;
}

.unread-count {
  background: #ef4444;
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.125rem 0.375rem;
  border-radius: 9999px;
  margin-left: 0.5rem;
}

.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.chat-header .contact-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.chat-header .contact-avatar img {
  width: 40px;
  height: 40px;
}

.contact-details h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.status-text {
  font-size: 0.875rem;
  color: #6b7280;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.message {
  max-width: 70%;
  display: flex;
}

.own-message {
  margin-left: auto;
}

.message-content {
  position: relative;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  background: #f3f4f6;
}

.own-message .message-content {
  background: #4f46e5;
  color: white;
}

.message-time {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
  display: block;
}

.own-message .message-time {
  color: rgba(255, 255, 255, 0.8);
}

.message-status {
  font-size: 0.75rem;
  margin-left: 0.25rem;
  color: rgba(255, 255, 255, 0.8);
}

.chat-input {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}

.input-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: flex-end;
}

textarea {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 1.5rem;
  resize: none;
  max-height: 120px;
  font-family: inherit;
  font-size: 0.875rem;
}

textarea:focus {
  outline: none;
  border-color: #4f46e5;
}

.input-actions {
  display: flex;
  gap: 0.5rem;
}

.attach-btn,
.send-btn {
  padding: 0.75rem;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.attach-btn {
  background: #f3f4f6;
  color: #6b7280;
}

.send-btn {
  background: #4f46e5;
  color: white;
}

.send-btn:disabled {
  background: #e5e7eb;
  cursor: not-allowed;
}

.selected-file {
  margin-top: 0.75rem;
  position: relative;
  display: inline-block;
}

.file-preview {
  max-height: 100px;
  border-radius: 0.5rem;
}

.remove-file {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #ef4444;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.no-chat-selected {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
}

.placeholder {
  text-align: center;
  color: #6b7280;
}

.placeholder i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #9ca3af;
}

.placeholder h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}

.loading-messages {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #6b7280;
  padding: 2rem;
}

.no-messages {
  text-align: center;
  color: #6b7280;
  padding: 2rem;
}

@media (max-width: 768px) {
  .contacts-sidebar {
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 50;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .contacts-sidebar.open {
    transform: translateX(0);
  }

  .chat-area {
    width: 100%;
  }

  .message {
    max-width: 85%;
  }
}
</style> 