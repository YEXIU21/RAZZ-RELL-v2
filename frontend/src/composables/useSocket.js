import { ref, onMounted, onUnmounted } from 'vue';
import { io } from 'socket.io-client';
import { useAuth } from './useAuth';
import { useNotifications } from './useNotifications';

const socket = ref(null);
const isConnected = ref(false);
const reconnectAttempts = ref(0);
const maxReconnectAttempts = 5;

export function useSocket() {
  const { user, getToken } = useAuth();
  const { showNotification } = useNotifications();

  const initSocket = async () => {
    try {
      const token = await getToken();
      const socketUrl = import.meta.env.VITE_SOCKET_URL || 'http://localhost:3001';
      
      if (socket.value?.connected) {
        console.log('Socket already connected');
        return;
      }

      socket.value = io(socketUrl, {
        auth: { token },
        reconnection: true,
        reconnectionAttempts: maxReconnectAttempts,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        timeout: 20000,
        transports: ['websocket', 'polling']
      });

      setupSocketListeners();
    } catch (error) {
      console.error('Socket initialization failed:', error);
      showNotification('Failed to connect to chat server', 'error');
    }
  };

  const setupSocketListeners = () => {
    if (!socket.value) return;

    socket.value.on('connect', () => {
      console.log('Socket connected');
      isConnected.value = true;
      reconnectAttempts.value = 0;
    });

    socket.value.on('disconnect', (reason) => {
      console.log('Socket disconnected:', reason);
      isConnected.value = false;
      
      if (reason === 'io server disconnect') {
        socket.value.connect();
      }
    });

    socket.value.on('connect_error', (error) => {
      console.error('Socket connection error:', error);
      reconnectAttempts.value++;

      if (reconnectAttempts.value >= maxReconnectAttempts) {
        showNotification('Unable to connect to chat server. Please try again later.', 'error');
      }
    });

    socket.value.on('error', (error) => {
      console.error('Socket error:', error);
      showNotification('An error occurred with the chat connection', 'error');
    });
  };

  const joinChat = (chatId) => {
    if (!isConnected.value || !socket.value) {
      console.warn('Socket not connected. Attempting to connect...');
      initSocket().then(() => {
        if (socket.value?.connected) {
          socket.value.emit('join_chat', { chatId });
        }
      });
      return;
    }
    socket.value.emit('join_chat', { chatId });
  };

  const leaveChat = (chatId) => {
    if (!isConnected.value || !socket.value) return;
    socket.value.emit('leave_chat', { chatId });
  };

  const sendMessage = (chatId, content) => {
    if (!isConnected.value || !socket.value) {
      console.warn('Socket not connected. Message not sent.');
      return;
    }
    
    socket.value.emit('message', {
      chatId,
      content,
      timestamp: new Date().toISOString()
    });
  };

  const emitTyping = (chatId, isTyping) => {
    if (!isConnected.value || !socket.value) return;
    socket.value.emit('typing', { chatId, isTyping });
  };

  const onNewMessage = (callback) => {
    if (!socket.value) return;
    socket.value.on('new_message', callback);
  };

  const onTypingStatus = (callback) => {
    if (!socket.value) return;
    socket.value.on('typing_status', callback);
  };

  const onUserJoined = (callback) => {
    if (!socket.value) return;
    socket.value.on('user_joined', callback);
  };

  const onUserLeft = (callback) => {
    if (!socket.value) return;
    socket.value.on('user_left', callback);
  };

  const disconnect = () => {
    if (socket.value) {
      socket.value.disconnect();
      socket.value = null;
      isConnected.value = false;
    }
  };

  onMounted(() => {
    if (user.value) {
      initSocket();
    }
  });

  onUnmounted(() => {
    disconnect();
  });

  return {
    isConnected,
    joinChat,
    leaveChat,
    sendMessage,
    emitTyping,
    onNewMessage,
    onTypingStatus,
    onUserJoined,
    onUserLeft,
    disconnect,
    initSocket
  };
} 