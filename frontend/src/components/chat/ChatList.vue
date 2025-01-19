<template>
  <div class="chat-list">
    <div class="chat-list-header">
      <h2>Active Chats</h2>
      <div class="header-actions">
        <button 
          class="filter-btn"
          @click="toggleFilter"
        >
          <i class="fas fa-filter"></i>
          Filter
        </button>
        <div v-if="showFilter" class="filter-dropdown">
          <label>
            <input 
              type="checkbox" 
              v-model="filters.waiting"
            > Waiting
          </label>
          <label>
            <input 
              type="checkbox" 
              v-model="filters.active"
            > Active
          </label>
          <label>
            <input 
              type="checkbox" 
              v-model="filters.closed"
            > Closed
          </label>
        </div>
      </div>
    </div>

    <div class="search-bar">
      <input 
        type="text"
        v-model="searchQuery"
        placeholder="Search chats..."
      >
    </div>

    <div class="chat-list-content" ref="chatListContent">
      <div v-if="loading" class="loading-state">
        <LoadingAnimation />
      </div>

      <template v-else>
        <div v-if="filteredChats.length === 0" class="empty-state">
          <i class="fas fa-comments"></i>
          <p>No chats found</p>
        </div>

        <div 
          v-for="chat in filteredChats" 
          :key="chat._id"
          class="chat-item"
          :class="{
            'active': selectedChatId === chat._id,
            'unread': hasUnreadMessages(chat)
          }"
          @click="selectChat(chat)"
        >
          <div class="chat-info">
            <div class="user-info">
              <span class="user-name">{{ chat.customer.name }}</span>
              <span class="chat-status" :class="chat.status">
                {{ chat.status }}
              </span>
            </div>
            <p class="last-message">
              {{ getLastMessage(chat) }}
            </p>
          </div>

          <div class="chat-meta">
            <span class="timestamp">
              {{ formatTime(chat.lastActivity) }}
            </span>
            <span 
              v-if="hasUnreadMessages(chat)" 
              class="unread-count"
            >
              {{ getUnreadCount(chat) }}
            </span>
          </div>
        </div>
      </template>
    </div>

    <div v-if="hasMoreChats" class="load-more">
      <button 
        @click="loadMore"
        :disabled="loadingMore"
      >
        {{ loadingMore ? 'Loading...' : 'Load More' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuth } from '@/composables/useAuth';
import { useSocket } from '@/composables/useSocket';
import LoadingAnimation from '@/components/ui/LoadingAnimation.vue';
import moment from 'moment';

const props = defineProps({
  selectedChatId: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['select-chat']);

const { user } = useAuth();
const socket = useSocket();

const chats = ref([]);
const loading = ref(true);
const loadingMore = ref(false);
const page = ref(1);
const hasMoreChats = ref(true);
const searchQuery = ref('');
const showFilter = ref(false);
const filters = ref({
  waiting: true,
  active: true,
  closed: false
});

const filteredChats = computed(() => {
  return chats.value
    .filter(chat => {
      // Filter by status
      if (!filters.value[chat.status.toLowerCase()]) {
        return false;
      }

      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        return (
          chat.customer.name.toLowerCase().includes(query) ||
          chat.messages.some(m => 
            m.content.toLowerCase().includes(query)
          )
        );
      }

      return true;
    })
    .sort((a, b) => new Date(b.lastActivity) - new Date(a.lastActivity));
});

onMounted(async () => {
  await fetchChats();
  setupSocketListeners();
});

const fetchChats = async () => {
  try {
    const response = await fetch(`/api/chats?page=${page.value}`);
    const data = await response.json();

    if (page.value === 1) {
      chats.value = data.chats;
    } else {
      chats.value = [...chats.value, ...data.chats];
    }

    hasMoreChats.value = data.hasMore;
    loading.value = false;
  } catch (error) {
    console.error('Error fetching chats:', error);
    loading.value = false;
  }
};

const loadMore = async () => {
  if (loadingMore.value || !hasMoreChats.value) return;

  loadingMore.value = true;
  page.value++;
  await fetchChats();
  loadingMore.value = false;
};

const setupSocketListeners = () => {
  socket.onNewMessage((data) => {
    const chatIndex = chats.value.findIndex(c => c._id === data.chatId);
    if (chatIndex !== -1) {
      const chat = chats.value[chatIndex];
      chat.messages.push(data.message);
      chat.lastActivity = new Date();
      
      // Move chat to top if not selected
      if (data.chatId !== props.selectedChatId) {
        chats.value.splice(chatIndex, 1);
        chats.value.unshift(chat);
      }
    }
  });

  socket.onChatStatusChange((data) => {
    const chat = chats.value.find(c => c._id === data.chatId);
    if (chat) {
      chat.status = data.status;
    }
  });
};

const selectChat = (chat) => {
  emit('select-chat', chat);
};

const hasUnreadMessages = (chat) => {
  if (chat._id === props.selectedChatId) return false;
  
  return chat.messages.some(message => 
    !message.read && message.sender !== user.value._id
  );
};

const getUnreadCount = (chat) => {
  return chat.messages.filter(message => 
    !message.read && message.sender !== user.value._id
  ).length;
};

const getLastMessage = (chat) => {
  const lastMessage = chat.messages[chat.messages.length - 1];
  return lastMessage ? lastMessage.content : 'No messages';
};

const formatTime = (timestamp) => {
  const date = moment(timestamp);
  const today = moment().startOf('day');
  
  if (date.isSame(today, 'day')) {
    return date.format('HH:mm');
  } else if (date.isSame(today.clone().subtract(1, 'day'), 'day')) {
    return 'Yesterday';
  } else {
    return date.format('MMM D');
  }
};

const toggleFilter = () => {
  showFilter.value = !showFilter.value;
};

// Watch for search query changes
watch(searchQuery, () => {
  page.value = 1;
  fetchChats();
});

// Watch for filter changes
watch(filters, () => {
  page.value = 1;
  fetchChats();
}, { deep: true });
</script>

<style scoped>
.chat-list {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: white;
  border-right: 1px solid #E5E7EB;
}

.chat-list-header {
  padding: 1rem;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-list-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.header-actions {
  position: relative;
}

.filter-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6B7280;
}

.filter-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  padding: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  z-index: 10;
}

.filter-dropdown label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  cursor: pointer;
}

.search-bar {
  padding: 1rem;
  border-bottom: 1px solid #E5E7EB;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  background: #F9FAFB;
}

.chat-list-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6B7280;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.chat-item {
  padding: 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.chat-item:hover {
  background: #F9FAFB;
}

.chat-item.active {
  background: #EFF6FF;
}

.chat-item.unread {
  background: #F3F4F6;
}

.chat-info {
  flex: 1;
  min-width: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.user-name {
  font-weight: 500;
  color: #111827;
}

.chat-status {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 1rem;
  text-transform: capitalize;
}

.chat-status.waiting {
  background: #FEF3C7;
  color: #92400E;
}

.chat-status.active {
  background: #D1FAE5;
  color: #065F46;
}

.chat-status.closed {
  background: #F3F4F6;
  color: #374151;
}

.last-message {
  color: #6B7280;
  font-size: 0.875rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.timestamp {
  font-size: 0.75rem;
  color: #6B7280;
}

.unread-count {
  background: #4F46E5;
  color: white;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 1rem;
  min-width: 1.5rem;
  text-align: center;
}

.load-more {
  padding: 1rem;
  text-align: center;
}

.load-more button {
  background: none;
  border: none;
  color: #4F46E5;
  cursor: pointer;
  padding: 0.5rem 1rem;
}

.load-more button:disabled {
  color: #9CA3AF;
  cursor: not-allowed;
}
</style> 