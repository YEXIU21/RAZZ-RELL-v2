<template>
  <div class="chat-list-container">
    <div class="chat-list-header">
      <h2>Customer Conversations</h2>
      <div class="header-actions">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search conversations..."
          />
        </div>
        <button class="filter-btn" @click="toggleFilter">
          <i class="fas fa-filter"></i>
          Filter
        </button>
      </div>
    </div>

    <div class="filter-options" v-if="showFilter">
      <label class="filter-option">
        <input 
          type="checkbox" 
          v-model="filters.unread" 
        />
        Unread messages
      </label>
      <label class="filter-option">
        <input 
          type="checkbox" 
          v-model="filters.active" 
        />
        Active chats
      </label>
      <select v-model="filters.sortBy" class="sort-select">
        <option value="recent">Most Recent</option>
        <option value="unread">Unread First</option>
        <option value="name">Name</option>
      </select>
    </div>

    <div class="chat-list">
      <div v-if="filteredChats.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>No conversations found</p>
      </div>

      <div 
        v-else
        v-for="chat in filteredChats" 
        :key="chat.id"
        class="chat-item"
        :class="{ 
          'active': selectedChatId === chat.id,
          'unread': chat.unread_count > 0
        }"
        @click="selectChat(chat)"
      >
        <div class="user-avatar">
          <img :src="chat.user.avatar || defaultAvatar" :alt="chat.user.name" />
          <span 
            class="status-dot"
            :class="{ 'online': chat.user.is_online }"
          ></span>
        </div>

        <div class="chat-info">
          <div class="chat-header">
            <h3>{{ chat.user.name }}</h3>
            <span class="time">{{ formatTime(chat.last_message?.created_at) }}</span>
          </div>
          
          <p class="last-message">
            {{ chat.last_message?.content || 'No messages yet' }}
          </p>

          <div class="chat-footer">
            <span class="message-count" v-if="chat.unread_count">
              {{ chat.unread_count }}
            </span>
            <span class="chat-status" v-if="chat.status === 'active'">
              <i class="fas fa-circle"></i> Active
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { format } from 'date-fns';

const props = defineProps({
  chats: {
    type: Array,
    default: () => []
  },
  selectedChatId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['select-chat']);

const defaultAvatar = '/src/assets/default-avatar.png';
const searchQuery = ref('');
const showFilter = ref(false);
const filters = ref({
  unread: false,
  active: false,
  sortBy: 'recent'
});

const toggleFilter = () => {
  showFilter.value = !showFilter.value;
};

const formatTime = (timestamp) => {
  if (!timestamp) return '';
  return format(new Date(timestamp), 'MMM d, h:mm a');
};

const filteredChats = computed(() => {
  let result = [...props.chats];

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(chat => 
      chat.user.name.toLowerCase().includes(query) ||
      chat.last_message?.content.toLowerCase().includes(query)
    );
  }

  // Apply filters
  if (filters.value.unread) {
    result = result.filter(chat => chat.unread_count > 0);
  }
  if (filters.value.active) {
    result = result.filter(chat => chat.status === 'active');
  }

  // Apply sorting
  switch (filters.value.sortBy) {
    case 'unread':
      result.sort((a, b) => b.unread_count - a.unread_count);
      break;
    case 'name':
      result.sort((a, b) => a.user.name.localeCompare(b.user.name));
      break;
    case 'recent':
    default:
      result.sort((a, b) => {
        const dateA = a.last_message?.created_at ? new Date(a.last_message.created_at) : new Date(0);
        const dateB = b.last_message?.created_at ? new Date(b.last_message.created_at) : new Date(0);
        return dateB - dateA;
      });
  }

  return result;
});

const selectChat = (chat) => {
  emit('select-chat', chat);
};
</script>

<style scoped>
.chat-list-container {
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
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.search-box {
  flex: 1;
  position: relative;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9CA3AF;
}

.search-box input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
}

.search-box input:focus {
  border-color: #4F46E5;
}

.filter-btn {
  padding: 0.75rem 1rem;
  background: #F3F4F6;
  border: none;
  border-radius: 0.5rem;
  color: #4B5563;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.filter-btn:hover {
  background: #E5E7EB;
}

.filter-options {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  background: #F9FAFB;
}

.filter-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #4B5563;
  cursor: pointer;
}

.sort-select {
  padding: 0.5rem;
  border: 1px solid #E5E7EB;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #4B5563;
  outline: none;
  cursor: pointer;
}

.chat-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0;
}

.empty-state {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #9CA3AF;
  gap: 1rem;
  padding: 2rem;
}

.empty-state i {
  font-size: 3rem;
}

.empty-state p {
  font-size: 0.875rem;
  margin: 0;
}

.chat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  border-left: 3px solid transparent;
}

.chat-item:hover {
  background: #F9FAFB;
}

.chat-item.active {
  background: #EEF2FF;
  border-left-color: #4F46E5;
}

.chat-item.unread {
  background: #F3F4F6;
}

.user-avatar {
  position: relative;
  flex-shrink: 0;
}

.user-avatar img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.status-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #9CA3AF;
  border: 2px solid white;
}

.status-dot.online {
  background: #10B981;
}

.chat-info {
  flex: 1;
  min-width: 0;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.chat-header h3 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}

.time {
  font-size: 0.75rem;
  color: #6B7280;
}

.last-message {
  margin: 0;
  font-size: 0.875rem;
  color: #6B7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-footer {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.message-count {
  background: #EF4444;
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.chat-status {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #10B981;
}

.chat-status i {
  font-size: 0.5rem;
}

@media (max-width: 768px) {
  .chat-list-container {
    width: 100%;
  }

  .filter-options {
    flex-direction: column;
  }
}
</style> 