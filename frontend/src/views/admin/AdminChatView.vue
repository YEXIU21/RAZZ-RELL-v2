<template>
  <div class="chat-container">
    <!-- Contact List Sidebar -->
    <div class="contacts-sidebar">
      <div class="sidebar-header">
        <h2>Messages</h2>
        <div class="role-filters">
          <button 
            v-for="role in ['admin', 'staff', 'users']" 
            :key="role"
            class="role-filter"
            :class="{ active: selectedRole === role }"
            @click="selectedRole = role"
          >
            {{ role.charAt(0).toUpperCase() + role.slice(1) }}
            <span v-if="getUnreadCountByRole(role)" class="unread-badge">
              {{ getUnreadCountByRole(role) }}
            </span>
          </button>
        </div>
      </div>
      
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search contacts..."
        >
      </div>

      <div class="contacts-list">
        <div 
          v-for="contact in filteredContacts" 
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
          <div class="header-actions">
            <button v-if="selectedContact.role === 'user'" class="view-booking-btn" @click="viewBooking">
              <i class="fas fa-calendar"></i>
              View Booking
            </button>
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
          <p>Choose from your existing conversations</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();

// Props & Emits
const props = defineProps({
  currentUserId: {
    type: [Number, String],
    required: true
  }
});

// State
const contacts = ref([]);
const selectedRole = ref('users');
const selectedContact = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(false);
const searchQuery = ref('');
const messagesContainer = ref(null);
const messageInput = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);
const filePreview = ref('');

// Computed
const filteredContacts = computed(() => {
  return contacts.value
    .filter(contact => contact.role === selectedRole.value)
    .filter(contact => {
      if (!searchQuery.value) return true;
      return contact.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
             contact.email?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

const canSendMessage = computed(() => {
  return newMessage.value.trim() || selectedFile.value;
});

// Methods
const fetchContacts = async () => {
  try {
    const response = await axios.get('/api/chat/contacts/all');
    contacts.value = response.data.contacts;
  } catch (error) {
    console.error('Error fetching contacts:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load contacts'
    });
  }
};

const getUnreadCountByRole = (role) => {
  return contacts.value
    .filter(contact => contact.role === role)
    .reduce((sum, contact) => sum + (contact.unreadCount || 0), 0);
};

const selectContact = (contact) => {
  if (!contact) return;
  selectedContact.value = contact;
  loadMessages(contact.id);
};

const loadMessages = async (contactId) => {
  if (!contactId) return;
  try {
    loading.value = true;
    const response = await axios.get(`/api/chat/messages/${contactId}`);
    messages.value = response.data.messages;
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error('Error loading messages:', error);
  } finally {
    loading.value = false;
  }
};

const viewBooking = () => {
  if (selectedContact.value?.bookingId) {
    router.push(`/admin/bookings/${selectedContact.value.bookingId}`);
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
  fetchContacts();
  // Poll for new messages and contact updates
  const pollInterval = setInterval(() => {
    if (selectedContact.value) {
      selectContact(selectedContact.value);
    }
    fetchContacts();
  }, 10000);

  return () => {
    clearInterval(pollInterval);
  };
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
/* Reuse the same styles as CustomerChatView.vue but add these additional styles */

.role-filters {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.role-filter {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 9999px;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.role-filter.active {
  background: #4f46e5;
  color: white;
}

.unread-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ef4444;
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.125rem 0.375rem;
  border-radius: 9999px;
  min-width: 18px;
  text-align: center;
}

.search-bar {
  padding: 1rem;
  position: relative;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 9999px;
  background: #f9fafb;
  font-size: 0.875rem;
}

.search-bar i {
  position: absolute;
  left: 2rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.view-booking-btn {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.view-booking-btn:hover {
  background: #e5e7eb;
}
</style> 