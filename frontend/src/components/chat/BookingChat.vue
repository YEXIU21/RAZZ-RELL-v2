<template>
  <div class="booking-chat">
    <div class="chat-header">
      <h3>Booking Chat</h3>
      <div class="chat-status" v-if="booking">
        <span class="status-badge" :class="booking.status">{{ booking.status }}</span>
      </div>
    </div>

    <div class="chat-messages" ref="messagesContainer">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        Loading messages...
      </div>

      <template v-else>
        <div v-for="message in messages" :key="message.id" 
          class="message" 
          :class="{ 
            'own-message': message.user_id === currentUserId,
            'system-message': message.is_system_message 
          }"
        >
          <div class="message-content">
            <div class="message-header" v-if="!message.is_system_message">
              <span class="sender-name">{{ message.user?.first_name }} {{ message.user?.last_name }}</span>
              <span class="message-time">{{ formatTime(message.created_at) }}</span>
            </div>
            
            <div class="message-text">{{ message.content }}</div>

            <!-- Payment Receipt Attachment -->
            <div v-if="message.attachment && message.attachment_type === 'payment_receipt'" class="attachment payment-receipt">
              <div class="receipt-preview">
                <img :src="getAttachmentUrl(message.attachment)" alt="Payment Receipt" @click="openImagePreview(message.attachment)">
              </div>
              <div class="receipt-actions">
                <a :href="getAttachmentUrl(message.attachment)" download class="download-btn">
                  <i class="fas fa-download"></i> Download Receipt
                </a>
              </div>
            </div>

            <!-- Other Attachments -->
            <div v-else-if="message.attachment" class="attachment">
              <div class="attachment-preview">
                <img :src="getAttachmentUrl(message.attachment)" alt="Attachment" @click="openImagePreview(message.attachment)">
              </div>
            </div>
          </div>
        </div>

        <div v-if="messages.length === 0" class="no-messages">
          No messages yet. Start the conversation!
        </div>
      </template>
    </div>

    <div class="chat-input">
      <div class="input-wrapper">
        <textarea 
          v-model="newMessage" 
          placeholder="Type your message..." 
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

    <!-- Image Preview Modal -->
    <div v-if="previewImage" class="image-preview-modal" @click="closeImagePreview">
      <div class="modal-content">
        <img :src="previewImage" alt="Preview">
        <button class="close-preview" @click="closeImagePreview">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  bookingId: {
    type: [Number, String],
    required: true
  },
  currentUserId: {
    type: [Number, String],
    required: true
  }
});

// State
const messages = ref([]);
const newMessage = ref('');
const loading = ref(true);
const booking = ref(null);
const messagesContainer = ref(null);
const messageInput = ref(null);
const fileInput = ref(null);
const selectedFile = ref(null);
const filePreview = ref('');
const previewImage = ref(null);

// Computed
const canSendMessage = computed(() => {
  return newMessage.value.trim() || selectedFile.value;
});

// Methods
const loadMessages = async () => {
  try {
    const response = await axios.get(`/api/bookings/${props.bookingId}/messages`);
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
    formData.append('booking_id', props.bookingId);
    formData.append('content', newMessage.value);
    
    if (selectedFile.value) {
      formData.append('attachment', selectedFile.value);
      formData.append('attachment_type', 'image');
    }

    const response = await axios.post('/api/messages', formData, {
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
  return new Date(timestamp).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  });
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

const getAttachmentUrl = (path) => {
  return `${import.meta.env.VITE_API_URL}/storage/${path}`;
};

const openImagePreview = (imagePath) => {
  previewImage.value = getAttachmentUrl(imagePath);
};

const closeImagePreview = () => {
  previewImage.value = null;
};

// Lifecycle
onMounted(() => {
  loadMessages();
  const pollInterval = setInterval(loadMessages, 10000); // Poll every 10 seconds

  return () => {
    clearInterval(pollInterval);
  };
});

// Watch for new messages to mark them as read
watch(messages, async (newMessages) => {
  const unreadMessageIds = newMessages
    .filter(m => !m.is_read && m.user_id !== props.currentUserId)
    .map(m => m.id);

  if (unreadMessageIds.length > 0) {
    try {
      await axios.put('/api/messages/read', {
        message_ids: unreadMessageIds
      });
    } catch (error) {
      console.error('Error marking messages as read:', error);
    }
  }
}, { deep: true });
</script>

<style scoped>
.booking-chat {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chat-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-header h3 {
  margin: 0;
  color: var(--text-color);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background: #FEF3C7;
  color: #92400E;
}

.status-badge.confirmed {
  background: #DEF7EC;
  color: #03543F;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #6B7280;
  padding: 2rem;
}

.message {
  max-width: 80%;
  align-self: flex-start;
}

.message.own-message {
  align-self: flex-end;
}

.message.system-message {
  align-self: center;
  max-width: 90%;
}

.message-content {
  background: #F3F4F6;
  padding: 0.75rem;
  border-radius: 12px;
  border-bottom-left-radius: 4px;
}

.own-message .message-content {
  background: #4F46E5;
  color: white;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 4px;
}

.system-message .message-content {
  background: #FEF3C7;
  color: #92400E;
  border-radius: 8px;
  font-style: italic;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.own-message .message-header {
  color: rgba(255, 255, 255, 0.9);
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.8;
}

.attachment {
  margin-top: 0.75rem;
  border-radius: 8px;
  overflow: hidden;
}

.attachment img {
  max-width: 100%;
  max-height: 200px;
  object-fit: contain;
  cursor: pointer;
  transition: transform 0.2s;
}

.attachment img:hover {
  transform: scale(1.02);
}

.receipt-actions {
  margin-top: 0.5rem;
}

.download-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  color: var(--text-color);
  text-decoration: none;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.download-btn:hover {
  background: #F9FAFB;
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
  border-radius: 8px;
  resize: none;
  max-height: 120px;
  font-family: inherit;
}

.input-actions {
  display: flex;
  gap: 0.5rem;
}

.attach-btn,
.send-btn {
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.attach-btn {
  background: #F3F4F6;
  color: #6B7280;
}

.send-btn {
  background: #4F46E5;
  color: white;
}

.send-btn:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

.selected-file {
  margin-top: 0.75rem;
  position: relative;
  display: inline-block;
}

.file-preview {
  max-height: 100px;
  border-radius: 8px;
}

.remove-file {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #EF4444;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.modal-content img {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-preview {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

.no-messages {
  text-align: center;
  color: #6B7280;
  padding: 2rem;
}

@media (max-width: 768px) {
  .message {
    max-width: 90%;
  }

  .download-btn {
    padding: 0.375rem 0.75rem;
  }
}
</style> 