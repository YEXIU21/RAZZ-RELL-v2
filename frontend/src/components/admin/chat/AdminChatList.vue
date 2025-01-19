<template>
  <div class="chat-list">
    <div class="chat-list-header">
      <h2>Active Chats</h2>
      <div class="filters">
        <select v-model="statusFilter" class="filter-select">
          <option value="all">All Chats</option>
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="closed">Closed</option>
        </select>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search chats..." 
          class="search-input"
        >
      </div>
    </div>

    <div class="chat-list-content">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading chats...</p>
      </div>

      <div v-else-if="filteredChats.length === 0" class="empty-state">
        <i class="fas fa-comments"></i>
        <p>No chats found</p>
      </div>

      <template v-else>
        <AdminChatCard
          v-for="chat in filteredChats"
          :key="chat.id"
          :chat="chat"
          :is-active="selectedChatId === chat.id"
          :staff-members="staffMembers"
          @click="$emit('select-chat', chat)"
          @transfer="handleTransfer"
          @close="handleClose"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminChatCard from './AdminChatCard.vue';

const props = defineProps({
  selectedChatId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['select-chat', 'transfer-chat', 'close-chat']);

const loading = ref(true);
const chats = ref([]);
const staffMembers = ref([]);
const statusFilter = ref('all');
const searchQuery = ref('');

const filteredChats = computed(() => {
  return chats.value
    .filter(chat => {
      // Status filter
      if (statusFilter.value !== 'all' && chat.status !== statusFilter.value) {
        return false;
      }
      
      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        return (
          chat.user.name.toLowerCase().includes(query) ||
          chat.last_message?.content.toLowerCase().includes(query)
        );
      }
      
      return true;
    })
    .sort((a, b) => {
      // Sort by unread count first
      if (a.unread_count !== b.unread_count) {
        return b.unread_count - a.unread_count;
      }
      // Then by last message time
      return new Date(b.last_message_at) - new Date(a.last_message_at);
    });
});

const fetchChats = async () => {
  try {
    loading.value = true;
    const response = await fetch('/api/admin/chats', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      chats.value = data.chats;
    }
  } catch (error) {
    console.error('Error fetching chats:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStaffMembers = async () => {
  try {
    const response = await fetch('/api/admin/staff', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      staffMembers.value = data.staff;
    }
  } catch (error) {
    console.error('Error fetching staff members:', error);
  }
};

const handleTransfer = async (data) => {
  try {
    const response = await fetch(`/api/admin/chats/${data.chatId}/transfer`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        staff_id: data.staffId,
        note: data.note
      })
    });

    if (response.ok) {
      // Update local chat data
      const chatIndex = chats.value.findIndex(c => c.id === data.chatId);
      if (chatIndex !== -1) {
        const updatedChat = await response.json();
        chats.value[chatIndex] = updatedChat.chat;
      }
      emit('transfer-chat', data);
    }
  } catch (error) {
    console.error('Error transferring chat:', error);
  }
};

const handleClose = async (data) => {
  try {
    const response = await fetch(`/api/admin/chats/${data.chatId}/close`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        note: data.note
      })
    });

    if (response.ok) {
      // Update local chat data
      const chatIndex = chats.value.findIndex(c => c.id === data.chatId);
      if (chatIndex !== -1) {
        const updatedChat = await response.json();
        chats.value[chatIndex] = updatedChat.chat;
      }
      emit('close-chat', data);
    }
  } catch (error) {
    console.error('Error closing chat:', error);
  }
};

// Set up polling for chat updates
let pollInterval;

const startPolling = () => {
  fetchChats();
  pollInterval = setInterval(fetchChats, 10000); // Poll every 10 seconds
};

onMounted(() => {
  fetchStaffMembers();
  startPolling();
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});
</script>

<style scoped>
.chat-list {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: white;
  border-right: 1px solid #E5E7EB;
}

.chat-list-header {
  padding: 1.5rem;
  border-bottom: 1px solid #E5E7EB;
}

.chat-list-header h2 {
  margin: 0 0 1rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1F2937;
}

.filters {
  display: flex;
  gap: 0.75rem;
}

.filter-select,
.search-input {
  padding: 0.5rem;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1F2937;
}

.filter-select {
  min-width: 120px;
}

.search-input {
  flex: 1;
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
  text-align: center;
  padding: 2rem;
}

.loading-state i,
.empty-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.loading-state p,
.empty-state p {
  margin: 0;
  font-size: 0.875rem;
}

/* Custom Scrollbar */
.chat-list-content {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E1 #F1F5F9;
}

.chat-list-content::-webkit-scrollbar {
  width: 6px;
}

.chat-list-content::-webkit-scrollbar-track {
  background: #F1F5F9;
}

.chat-list-content::-webkit-scrollbar-thumb {
  background-color: #CBD5E1;
  border-radius: 3px;
}

@media (max-width: 768px) {
  .chat-list {
    border-right: none;
  }

  .chat-list-header {
    padding: 1rem;
  }

  .filters {
    flex-direction: column;
  }

  .chat-list-content {
    padding: 0.75rem;
  }
}
</style> 