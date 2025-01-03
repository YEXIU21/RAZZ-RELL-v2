`<template>
  <div class="chat-history">
    <div class="chat-header">
      <div class="customer-info">
        <img :src="chat.customer.avatar" :alt="chat.customer.name" class="avatar">
        <div class="info">
          <h3>{{ chat.customer.name }}</h3>
          <span class="email">{{ chat.customer.email }}</span>
        </div>
      </div>
      <div class="chat-meta">
        <div class="meta-item">
          <i class="fas fa-clock"></i>
          <span>Started: {{ formatDate(chat.createdAt) }}</span>
        </div>
        <div class="meta-item">
          <i class="fas fa-tag"></i>
          <span>{{ chat.category }}</span>
        </div>
        <div class="meta-item">
          <i class="fas fa-flag"></i>
          <span>Priority: {{ chat.priority }}</span>
        </div>
      </div>
    </div>

    <div class="chat-content" ref="chatContent">
      <div v-if="loading" class="loading-state">
        <LoadingAnimation />
      </div>

      <template v-else>
        <div v-if="messages.length === 0" class="no-messages">
          No messages in this conversation yet
        </div>

        <div v-else class="messages">
          <div
            v-for="message in messages"
            :key="message._id"
            :class="['message', message.sender.role]"
          >
            <div class="message-header">
              <div class="sender-info">
                <img :src="message.sender.avatar" :alt="message.sender.name" class="avatar small">
                <span class="name">{{ message.sender.name }}</span>
                <span class="role">{{ formatRole(message.sender.role) }}</span>
              </div>
              <span class="timestamp">{{ formatTime(message.timestamp) }}</span>
            </div>

            <div class="message-content">
              <template v-if="message.type === 'text'">
                {{ message.content }}
              </template>

              <template v-else-if="message.type === 'image'">
                <img
                  :src="message.content"
                  :alt="'Image sent by ' + message.sender.name"
                  class="message-image"
                  @click="openImagePreview(message.content)"
                >
              </template>

              <template v-else-if="message.type === 'file'">
                <div class="file-attachment">
                  <i class="fas fa-file"></i>
                  <span class="file-name">{{ message.fileName }}</span>
                  <button @click="downloadFile(message)" class="download-btn">
                    <i class="fas fa-download"></i>
                  </button>
                </div>
              </template>
            </div>

            <div v-if="message.edited" class="edit-indicator">
              (edited)
            </div>
          </div>
        </div>

        <div v-if="hasMoreMessages" class="load-more">
          <button
            @click="loadMoreMessages"
            :disabled="loadingMore"
            class="load-more-btn"
          >
            <i v-if="loadingMore" class="fas fa-spinner fa-spin"></i>
            <span v-else>Load More Messages</span>
          </button>
        </div>
      </template>
    </div>

    <div v-if="chat.status === 'closed'" class="chat-closed">
      <div class="closed-info">
        <i class="fas fa-lock"></i>
        <span>This chat was closed on {{ formatDate(chat.closedAt) }}</span>
      </div>
      <div v-if="chat.closureNote" class="closure-note">
        Note: {{ chat.closureNote }}
      </div>
    </div>

    <div v-else class="chat-input">
      <div class="input-toolbar">
        <button @click="openFileInput" class="toolbar-btn">
          <i class="fas fa-paperclip"></i>
        </button>
        <button @click="openEmojiPicker" class="toolbar-btn">
          <i class="fas fa-smile"></i>
        </button>
      </div>

      <div class="input-container">
        <textarea
          v-model="newMessage"
          placeholder="Type your message..."
          @keydown.enter.prevent="sendMessage"
          @input="autoGrow"
          ref="messageInput"
        ></textarea>
        <button
          @click="sendMessage"
          :disabled="!newMessage.trim()"
          class="send-btn"
        >
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>

      <input
        type="file"
        ref="fileInput"
        @change="handleFileUpload"
        style="display: none"
        multiple
      >
    </div>

    <div v-if="showEmojiPicker" class="emoji-picker-container">
      <ChatEmoji @select="addEmoji" @close="showEmojiPicker = false" />
    </div>

    <div v-if="showImagePreview" class="image-preview-overlay" @click="closeImagePreview">
      <div class="image-preview-container">
        <img :src="previewImage" alt="Preview">
        <button class="close-preview-btn" @click="closeImagePreview">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import LoadingAnimation from '@/components/ui/LoadingAnimation.vue';
import ChatEmoji from '@/components/chat/ChatEmoji.vue';

export default {
  name: 'ChatHistory',
  components: {
    LoadingAnimation,
    ChatEmoji
  },
  props: {
    chat: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const { showNotification } = useNotifications();
    
    // Refs
    const chatContent = ref(null);
    const messageInput = ref(null);
    const fileInput = ref(null);
    const loading = ref(true);
    const loadingMore = ref(false);
    const messages = ref([]);
    const newMessage = ref('');
    const showEmojiPicker = ref(false);
    const showImagePreview = ref(false);
    const previewImage = ref('');
    const page = ref(1);
    const hasMoreMessages = ref(true);

    // Methods
    const loadMessages = async () => {
      try {
        const response = await fetch(`/api/chats/${props.chat._id}/messages?page=${page.value}`);
        const data = await response.json();
        
        messages.value = [...messages.value, ...data.messages];
        hasMoreMessages.value = data.hasMore;
        
        if (page.value === 1) {
          await nextTick();
          scrollToBottom();
        }
      } catch (error) {
        showNotification('Error loading messages', 'error');
      } finally {
        loading.value = false;
        loadingMore.value = false;
      }
    };

    const loadMoreMessages = async () => {
      if (loadingMore.value || !hasMoreMessages.value) return;
      
      loadingMore.value = true;
      page.value++;
      await loadMessages();
    };

    const sendMessage = async () => {
      if (!newMessage.value.trim()) return;

      try {
        const response = await fetch(`/api/chats/${props.chat._id}/messages`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ content: newMessage.value, type: 'text' })
        });

        if (!response.ok) throw new Error('Failed to send message');

        const message = await response.json();
        messages.value.push(message);
        newMessage.value = '';
        
        await nextTick();
        scrollToBottom();
      } catch (error) {
        showNotification('Error sending message', 'error');
      }
    };

    const handleFileUpload = async (event) => {
      const files = event.target.files;
      if (!files.length) return;

      const formData = new FormData();
      Array.from(files).forEach(file => {
        formData.append('files', file);
      });

      try {
        const response = await fetch(`/api/chats/${props.chat._id}/attachments`, {
          method: 'POST',
          body: formData
        });

        if (!response.ok) throw new Error('Failed to upload files');

        const uploadedFiles = await response.json();
        messages.value.push(...uploadedFiles);
        
        await nextTick();
        scrollToBottom();
      } catch (error) {
        showNotification('Error uploading files', 'error');
      } finally {
        fileInput.value.value = '';
      }
    };

    const openFileInput = () => {
      fileInput.value.click();
    };

    const openEmojiPicker = () => {
      showEmojiPicker.value = true;
    };

    const addEmoji = (emoji) => {
      newMessage.value += emoji;
      messageInput.value.focus();
    };

    const openImagePreview = (imageUrl) => {
      previewImage.value = imageUrl;
      showImagePreview.value = true;
    };

    const closeImagePreview = () => {
      showImagePreview.value = false;
      previewImage.value = '';
    };

    const downloadFile = async (message) => {
      try {
        const response = await fetch(message.content);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = message.fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      } catch (error) {
        showNotification('Error downloading file', 'error');
      }
    };

    const autoGrow = () => {
      const textarea = messageInput.value;
      textarea.style.height = 'auto';
      textarea.style.height = textarea.scrollHeight + 'px';
    };

    const scrollToBottom = () => {
      const container = chatContent.value;
      container.scrollTop = container.scrollHeight;
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleString();
    };

    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleTimeString();
    };

    const formatRole = (role) => {
      return role.charAt(0).toUpperCase() + role.slice(1);
    };

    // Watchers
    watch(() => props.chat._id, () => {
      messages.value = [];
      page.value = 1;
      hasMoreMessages.value = true;
      loading.value = true;
      loadMessages();
    });

    // Lifecycle
    onMounted(() => {
      loadMessages();
    });

    return {
      chatContent,
      messageInput,
      fileInput,
      loading,
      loadingMore,
      messages,
      newMessage,
      showEmojiPicker,
      showImagePreview,
      previewImage,
      hasMoreMessages,
      loadMoreMessages,
      sendMessage,
      handleFileUpload,
      openFileInput,
      openEmojiPicker,
      addEmoji,
      openImagePreview,
      closeImagePreview,
      downloadFile,
      autoGrow,
      formatDate,
      formatTime,
      formatRole
    };
  }
};
</script>

<style scoped>
.chat-history {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chat-header {
  padding: 16px;
  border-bottom: 1px solid #E5E7EB;
}

.customer-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar.small {
  width: 24px;
  height: 24px;
}

.info h3 {
  margin: 0;
  font-size: 16px;
  color: #111827;
}

.email {
  font-size: 14px;
  color: #6B7280;
}

.chat-meta {
  display: flex;
  gap: 16px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #6B7280;
}

.meta-item i {
  color: #9CA3AF;
}

.chat-content {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.no-messages {
  text-align: center;
  padding: 32px;
  color: #6B7280;
  font-style: italic;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.message {
  max-width: 70%;
}

.message.customer {
  align-self: flex-start;
}

.message.agent {
  align-self: flex-end;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.sender-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.name {
  font-size: 14px;
  font-weight: 500;
  color: #111827;
}

.role {
  font-size: 12px;
  color: #6B7280;
}

.timestamp {
  font-size: 12px;
  color: #9CA3AF;
}

.message-content {
  padding: 12px;
  border-radius: 12px;
  background: #F3F4F6;
  font-size: 14px;
  color: #374151;
}

.message.agent .message-content {
  background: #EEF2FF;
  color: #1E40AF;
}

.message-image {
  max-width: 100%;
  border-radius: 8px;
  cursor: pointer;
}

.file-attachment {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
}

.file-name {
  flex: 1;
  font-size: 14px;
  color: #374151;
}

.download-btn {
  padding: 4px 8px;
  background: none;
  border: none;
  color: #4F46E5;
  cursor: pointer;
}

.download-btn:hover {
  color: #4338CA;
}

.edit-indicator {
  font-size: 12px;
  color: #9CA3AF;
  margin-top: 4px;
}

.load-more {
  display: flex;
  justify-content: center;
  margin-top: 16px;
}

.load-more-btn {
  padding: 8px 16px;
  background: #F3F4F6;
  border: none;
  border-radius: 6px;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.load-more-btn:hover:not(:disabled) {
  background: #E5E7EB;
}

.load-more-btn:disabled {
  color: #9CA3AF;
  cursor: not-allowed;
}

.chat-closed {
  padding: 16px;
  background: #F3F4F6;
  border-top: 1px solid #E5E7EB;
}

.closed-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #374151;
  font-size: 14px;
}

.closure-note {
  margin-top: 8px;
  font-size: 14px;
  color: #6B7280;
  font-style: italic;
}

.chat-input {
  padding: 16px;
  border-top: 1px solid #E5E7EB;
}

.input-toolbar {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.toolbar-btn {
  padding: 8px;
  background: none;
  border: none;
  border-radius: 6px;
  color: #6B7280;
  cursor: pointer;
  transition: all 0.2s;
}

.toolbar-btn:hover {
  background: #F3F4F6;
  color: #374151;
}

.input-container {
  display: flex;
  gap: 8px;
}

textarea {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  resize: none;
  min-height: 40px;
  max-height: 120px;
  font-size: 14px;
  line-height: 1.5;
}

textarea:focus {
  outline: none;
  border-color: #4F46E5;
}

.send-btn {
  padding: 8px 16px;
  background: #4F46E5;
  border: none;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  transition: background-color 0.2s;
}

.send-btn:hover:not(:disabled) {
  background: #4338CA;
}

.send-btn:disabled {
  background: #E5E7EB;
  color: #9CA3AF;
  cursor: not-allowed;
}

.emoji-picker-container {
  position: absolute;
  bottom: 100%;
  right: 0;
  margin-bottom: 8px;
}

.image-preview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.image-preview-container {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.image-preview-container img {
  max-width: 100%;
  max-height: 90vh;
  border-radius: 8px;
}

.close-preview-btn {
  position: absolute;
  top: -40px;
  right: 0;
  padding: 8px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .chat-meta {
    flex-direction: column;
    gap: 8px;
  }

  .message {
    max-width: 85%;
  }

  .input-toolbar {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 8px 0;
  }
}
</style>` 