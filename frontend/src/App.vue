<template>
  <div class="app">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    
    <nav-bar v-if="showNav" />
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page" mode="out-in">
          <div :key="$route.path">
            <component :is="Component" />
          </div>
        </transition>
      </router-view>
    </main>
    
    <!-- Show chat for authenticated non-admin users -->
    <div v-if="shouldShowChat" class="chat-container">
      <FloatingChatButton 
        :unreadCount="unreadCount"
        :isOpen="isChatOpen"
        @toggle-chat="toggleChat"
      />
      <ChatInterface
        v-if="isChatOpen"
        :isOpen="true"
        :currentUserId="currentUserId"
        :messages="messages"
        :adminAvatar="adminAvatar"
        :isAdminOnline="isAdminOnline"
        @close="toggleChat"
        @send-message="handleSendMessage"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import NavBar from '@/components/layout/Navbar.vue';
import FloatingChatButton from '@/components/chat/FloatingChatButton.vue';
import ChatInterface from '@/components/chat/ChatInterface.vue';
import defaultAvatar from '@/assets/default-profile.png';
import { io } from 'socket.io-client';
import '@/assets/css/swal.css';

const route = useRoute();
const showNav = computed(() => !['login', 'register'].includes(route.name));
const isAdminRoute = computed(() => route.path.startsWith('/admin'));
const isLoginPage = computed(() => ['login', 'register'].includes(route.name));

// Authentication and user role checks
const isAuthenticated = computed(() => {
  try {
    const token = localStorage.getItem('auth_token');
    const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
    return !!(token && userInfo && userInfo.id);
  } catch (error) {
    console.error('Auth check error:', error);
    return false;
  }
});

const isCustomer = computed(() => {
  try {
    const userRole = localStorage.getItem('user_role')?.toLowerCase();
    const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
    console.log('User Role Check:', {
      userRole,
      userInfoRole: userInfo.role?.toLowerCase()
    });
    return userRole === 'user' || userInfo.role?.toLowerCase() === 'user';
  } catch (error) {
    console.error('Role check error:', error);
    return false;
  }
});

// Computed property to determine if chat should be shown
const shouldShowChat = computed(() => {
  const auth = isAuthenticated.value;
  const customer = isCustomer.value;
  const notAdmin = !isAdminRoute.value;
  
  console.log('Chat Visibility Debug:', {
    isAuthenticated: auth,
    isCustomer: customer,
    notAdminRoute: notAdmin,
    token: !!localStorage.getItem('auth_token'),
    userInfo: localStorage.getItem('user_info'),
    currentRoute: route.path
  });
  
  return auth && customer && notAdmin;
});

// Chat state
const isChatOpen = ref(false);
const unreadCount = ref(0);
const messages = ref([]);
const adminAvatar = defaultAvatar;
const isAdminOnline = ref(true);

const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

const toggleChat = () => {
  isChatOpen.value = !isChatOpen.value;
  if (isChatOpen.value) {
    unreadCount.value = 0; // Reset unread count when opening chat
  }
};

const socket = ref(null);

const handleSendMessage = async (message) => {
  try {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    // Add message to local messages immediately for better UX
    const newMessage = {
      id: Date.now(),
      content: message,
      sender_id: currentUserId.value,
      created_at: new Date().toISOString(),
      pending: true
    };
    
    messages.value.push(newMessage);

    // Send message through API
    const response = await fetch('/api/chat/send', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        content: message,
        receiver_id: 1 // Assuming admin has ID 1, adjust as needed
      })
    });

    if (response.ok) {
      const data = await response.json();
      // Update the pending message with the actual message data
      const index = messages.value.findIndex(m => m.id === newMessage.id);
      if (index !== -1) {
        messages.value[index] = { ...data.message, pending: false };
      }
    } else {
      // Handle error - remove the pending message
      messages.value = messages.value.filter(m => m.id !== newMessage.id);
      console.error('Failed to send message:', await response.text());
    }
    
  } catch (error) {
    console.error('Error sending message:', error);
  }
};

// Initialize chat data
onMounted(async () => {
  if (shouldShowChat.value) {
    try {
      // Initialize socket connection
      socket.value = io(import.meta.env.VITE_SOCKET_URL || 'http://localhost:3001', {
        auth: {
          token: localStorage.getItem('auth_token')
        }
      });
      
      // Listen for new messages
      socket.value.on('receive_message', (message) => {
        messages.value.push(message);
        if (!isChatOpen.value) {
          unreadCount.value++;
        }
      });

      // Listen for admin status
      socket.value.on('admin_status', (status) => {
        isAdminOnline.value = status.online;
      });

      // Listen for connection errors
      socket.value.on('connect_error', (error) => {
        console.error('Socket connection error:', error);
      });

      // Fetch initial messages
      const response = await fetch('/api/chat/messages', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json'
        }
      });
      
      if (response.ok) {
        const data = await response.json();
        messages.value = data.messages || [];
      }

      // Get unread count
      const unreadResponse = await fetch('/api/chat/unread-count', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json'
        }
      });

      if (unreadResponse.ok) {
        const data = await unreadResponse.json();
        unreadCount.value = data.count || 0;
      }
    } catch (error) {
      console.error('Error initializing chat:', error);
    }
  }
});

// Add cleanup in onUnmounted
onUnmounted(() => {
  if (socket.value) {
    socket.value.disconnect();
  }
});
</script>

<style>
/* CSS Reset */
*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Base layout */
.app {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  background-color: var(--background-color);
  color: var(--text-color);
  font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.main-content {
  flex: 1;
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1920px;
  margin: 0 auto;
  padding: 1rem;
  background-color: var(--background-color);
}

/* Dark mode specific styles */
:root[data-theme="dark"] {
  .app {
    background-color: var(--background-color);
    color: var(--text-color);
  }

  .main-content {
    background-color: var(--background-color);
  }

  .chat-interface {
    background-color: var(--card-background);
    border: 1px solid var(--border-color);
  }
}

/* Page transitions with browser prefixes */
.page-enter-active,
.page-leave-active {
  -webkit-transition: opacity 0.3s ease, transform 0.3s ease;
  -moz-transition: opacity 0.3s ease, transform 0.3s ease;
  -o-transition: opacity 0.3s ease, transform 0.3s ease;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.page-enter-from {
  opacity: 0;
  -webkit-transform: translateX(-20px);
  -moz-transform: translateX(-20px);
  -ms-transform: translateX(-20px);
  transform: translateX(-20px);
}

.page-leave-to {
  opacity: 0;
  -webkit-transform: translateX(20px);
  -moz-transform: translateX(20px);
  -ms-transform: translateX(20px);
  transform: translateX(20px);
}

/* Media Queries */
@media screen and (max-width: 768px) {
  .main-content {
    padding: 0.5rem;
  }
}

/* Custom Scrollbar with fallback */
* {
  scrollbar-width: thin;
  scrollbar-color: var(--primary) var(--light);
}

/* Webkit scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--light);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background-color: var(--primary);
  border-radius: 4px;
  border: 2px solid var(--light);
}

/* Selection */
::selection {
  background-color: var(--primary);
  color: var(--white);
}

::-moz-selection {
  background-color: var(--primary);
  color: var(--white);
}

/* Focus styles */
:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* Image optimization */
img {
  max-width: 100%;
  height: auto;
  display: block;
}

/* Font rendering optimization */
html {
  -webkit-text-size-adjust: 100%;
  -webkit-tap-highlight-color: transparent;
  scroll-behavior: smooth;
}

body {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  line-height: 1.5;
  font-synthesis: none;
}

/* Ensure chat components are above main content */
.floating-chat-container,
.chat-interface {
  position: fixed;
  z-index: 9999;
}

/* Add these styles at the end */
.chat-container {
  position: fixed;
  bottom: 0;
  right: 0;
  z-index: 99999;
  width: auto;
  height: auto;
  pointer-events: none;
  display: block !important;
}

.chat-container > * {
  pointer-events: auto;
}

/* Ensure chat components are above main content */
.floating-chat-container {
  position: fixed;
  bottom: 2rem !important;
  right: 2rem !important;
  z-index: 99999;
  width: auto;
  height: auto;
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.floating-chat-button {
  display: flex !important;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #4F46E5;
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.chat-interface {
  position: fixed;
  bottom: 5rem;
  right: 2rem;
  z-index: 99998;
  width: 380px;
  max-height: 600px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}
</style> 