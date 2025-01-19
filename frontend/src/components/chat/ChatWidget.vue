<template>
  <div class="chat-widget" :class="{ 'widget-open': isOpen }">
    <!-- Chat Button -->
    <button @click="toggleWidget" class="chat-button" :class="{ 'has-notification': hasUnread }">
      <i class="fas" :class="buttonIcon"></i>
      <span v-if="hasUnread" class="notification-badge">{{ unreadCount }}</span>
    </button>

    <!-- Chat Window -->
    <div v-show="isOpen" class="chat-window">
      <!-- Header -->
      <div class="chat-header">
        <div class="header-info">
          <img src="@/assets/logo.png" alt="Razz Rell Events" class="company-logo">
          <div class="header-text">
            <h3>Razz Rell Events Support</h3>
            <p v-if="activeChat && activeChat.agent">
              Speaking with {{ activeChat.agent.name }}
            </p>
            <p v-else>
              {{ isAuthenticated ? 'Start a conversation' : 'Sign in to chat' }}
            </p>
          </div>
        </div>
        <button @click="toggleWidget" class="close-button">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Content -->
      <div class="chat-content">
        <div v-if="!isAuthenticated" class="auth-prompt">
          <p>Please sign in to start a conversation with our support team.</p>
          <router-link to="/login" class="auth-button">Sign In</router-link>
        </div>

        <template v-else>
          <!-- Active Chat -->
          <div v-if="activeChat" class="chat-messages" ref="messagesContainer">
            <div v-for="message in activeChat.messages" :key="message._id" 
                 :class="['message', { 'own-message': isOwnMessage(message) }]">
              <div class="message-content">
                {{ message.content }}
                <span class="message-time">{{ formatTime(message.timestamp) }}</span>
              </div>
            </div>
            <div v-if="isTyping" class="typing-indicator">
              Agent is typing...
            </div>
          </div>

          <!-- Start New Chat -->
          <div v-else-if="!showNewChatForm" class="start-chat">
            <p>How can we help you today?</p>
            <button @click="showNewChatForm = true" class="start-chat-button">
              Start New Conversation
            </button>
          </div>

          <!-- New Chat Form -->
          <form v-else @submit.prevent="startNewChat" class="new-chat-form">
            <div class="form-group">
              <label for="subject">Subject</label>
              <input
                id="subject"
                v-model="newChat.subject"
                type="text"
                required
                placeholder="What's your inquiry about?"
              >
            </div>
            <div class="form-group">
              <label for="category">Category</label>
              <select id="category" v-model="newChat.category" required>
                <option value="general">General Inquiry</option>
                <option value="booking">Booking Related</option>
                <option value="support">Technical Support</option>
                <option value="feedback">Feedback</option>
              </select>
            </div>
            <div class="form-group">
              <label for="message">Message</label>
              <textarea
                id="message"
                v-model="newChat.message"
                required
                placeholder="Type your message here..."
              ></textarea>
            </div>
            <div class="form-actions">
              <button type="button" @click="showNewChatForm = false" class="cancel-button">
                Cancel
              </button>
              <button type="submit" :disabled="sending" class="submit-button">
                {{ sending ? 'Starting Chat...' : 'Start Chat' }}
              </button>
            </div>
          </form>
        </template>
      </div>

      <!-- Input Area -->
      <div v-if="activeChat && activeChat.status !== 'closed'" class="chat-input">
        <textarea
          v-model="messageText"
          @keydown.enter.prevent="sendMessage"
          @input="handleTyping"
          placeholder="Type a message..."
          :disabled="sending"
        ></textarea>
        <button @click="sendMessage" :disabled="!canSend" class="send-button">
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
      <div v-else-if="activeChat?.status === 'closed'" class="chat-closed">
        <p>This conversation has ended.</p>
        <button @click="startNewChat" class="new-chat-button">
          Start New Conversation
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useSocket } from '@/composables/useSocket';
import { useAuth } from '@/composables/useAuth';
import { useNotifications } from '@/composables/useNotifications';

export default {
  name: 'ChatWidget',
  setup() {
    const { isAuthenticated, user } = useAuth();
    const { socket } = useSocket();
    const { showNotification } = useNotifications();

    // State
    const isOpen = ref(false);
    const activeChat = ref(null);
    const messageText = ref('');
    const sending = ref(false);
    const showNewChatForm = ref(false);
    const newChat = ref({
      subject: '',
      category: 'general',
      message: ''
    });
    const isTyping = ref(false);
    const typingTimeout = ref(null);
    const unreadCount = ref(0);
    const messagesContainer = ref(null);

    // Computed
    const hasUnread = computed(() => unreadCount.value > 0);
    
    const buttonIcon = computed(() => 
      isOpen.value ? 'fa-times' : 'fa-comments'
    );

    const canSend = computed(() => 
      messageText.value.trim() && !sending.value
    );

    // Methods
    const toggleWidget = () => {
      isOpen.value = !isOpen.value;
      if (isOpen.value) {
        unreadCount.value = 0;
      }
    };

    const startNewChat = async () => {
      if (sending.value) return;
      sending.value = true;

      try {
        const response = await fetch('/api/chat', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            subject: newChat.value.subject,
            category: newChat.value.category,
            message: newChat.value.message
          })
        });

        const data = await response.json();
        activeChat.value = data.chat;
        showNewChatForm.value = false;
        newChat.value = { subject: '', category: 'general', message: '' };
        
        socket.emit('join_chat', activeChat.value._id);
      } catch (error) {
        showNotification('Error starting chat', 'error');
      } finally {
        sending.value = false;
      }
    };

    const sendMessage = async () => {
      if (!canSend.value) return;
      sending.value = true;

      try {
        const response = await fetch(`/api/chat/${activeChat.value._id}/messages`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ content: messageText.value })
        });

        const data = await response.json();
        activeChat.value.messages.push(data.message);
        messageText.value = '';
        scrollToBottom();
      } catch (error) {
        showNotification('Error sending message', 'error');
      } finally {
        sending.value = false;
      }
    };

    const handleTyping = () => {
      socket.emit('typing', {
        chatId: activeChat.value._id,
        user: user.value.name
      });
    };

    const scrollToBottom = () => {
      if (messagesContainer.value) {
        setTimeout(() => {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }, 100);
      }
    };

    const isOwnMessage = (message) => {
      return message.sender === user.value._id;
    };

    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
    };

    // Socket event handlers
    const handleNewMessage = (message) => {
      if (activeChat.value && message.chatId === activeChat.value._id) {
        activeChat.value.messages.push(message);
        scrollToBottom();
        if (!isOpen.value && !isOwnMessage(message)) {
          unreadCount.value++;
        }
      }
    };

    const handleTypingEvent = ({ chatId, user: typingUser }) => {
      if (activeChat.value && chatId === activeChat.value._id && typingUser !== user.value.name) {
        isTyping.value = true;
        if (typingTimeout.value) clearTimeout(typingTimeout.value);
        typingTimeout.value = setTimeout(() => {
          isTyping.value = false;
        }, 3000);
      }
    };

    const handleAgentAssigned = (data) => {
      if (activeChat.value && data.chatId === activeChat.value._id) {
        activeChat.value.agent = data.agent;
        showNotification(`${data.agent.name} has joined the chat`, 'success');
      }
    };

    // Lifecycle hooks
    onMounted(() => {
      socket.on('new_message', handleNewMessage);
      socket.on('typing', handleTypingEvent);
      socket.on('agent_assigned', handleAgentAssigned);
    });

    onUnmounted(() => {
      socket.off('new_message', handleNewMessage);
      socket.off('typing', handleTypingEvent);
      socket.off('agent_assigned', handleAgentAssigned);
      if (typingTimeout.value) clearTimeout(typingTimeout.value);
    });

    return {
      isOpen,
      activeChat,
      messageText,
      sending,
      showNewChatForm,
      newChat,
      isTyping,
      unreadCount,
      messagesContainer,
      hasUnread,
      buttonIcon,
      canSend,
      isAuthenticated,
      toggleWidget,
      startNewChat,
      sendMessage,
      handleTyping,
      isOwnMessage,
      formatTime
    };
  }
};
</script>

<style scoped>
.chat-widget {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}

.chat-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #4F46E5;
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  position: relative;
  transition: transform 0.2s;
}

.chat-button:hover {
  transform: scale(1.05);
}

.chat-button.has-notification {
  animation: pulse 2s infinite;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #EF4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 350px;
  height: 500px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1rem;
  background: #4F46E5;
  color: white;
  border-radius: 12px 12px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.company-logo {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.header-text h3 {
  margin: 0;
  font-size: 1rem;
}

.header-text p {
  margin: 0;
  font-size: 0.875rem;
  opacity: 0.8;
}

.close-button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
}

.chat-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.auth-prompt {
  text-align: center;
  padding: 2rem;
}

.auth-button {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #4F46E5;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  margin-top: 1rem;
}

.chat-messages {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  max-width: 80%;
}

.message.own-message {
  align-self: flex-end;
}

.message-content {
  background: #F3F4F6;
  padding: 0.75rem;
  border-radius: 12px;
  position: relative;
}

.own-message .message-content {
  background: #4F46E5;
  color: white;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
  margin-top: 0.25rem;
  display: block;
}

.typing-indicator {
  font-style: italic;
  color: #6B7280;
  padding: 0.5rem;
}

.chat-input {
  padding: 1rem;
  border-top: 1px solid #E5E7EB;
  display: flex;
  gap: 0.5rem;
}

textarea {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  resize: none;
  height: 40px;
  font-family: inherit;
}

.send-button {
  padding: 0.75rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.send-button:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

.chat-closed {
  padding: 1rem;
  text-align: center;
  border-top: 1px solid #E5E7EB;
}

.new-chat-button {
  padding: 0.75rem 1.5rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 0.5rem;
}

.start-chat {
  text-align: center;
  padding: 2rem;
}

.start-chat-button {
  padding: 0.75rem 1.5rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
}

.new-chat-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-family: inherit;
}

.form-group textarea {
  height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.cancel-button {
  padding: 0.75rem 1.5rem;
  background: #F3F4F6;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.submit-button {
  padding: 0.75rem 1.5rem;
  background: #4F46E5;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.submit-button:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(79, 70, 229, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
  }
}

@media (max-width: 768px) {
  .chat-widget {
    bottom: 1rem;
    right: 1rem;
  }

  .chat-window {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    border-radius: 0;
  }

  .chat-header {
    border-radius: 0;
  }
}
</style> 