<template>
  <div class="chat-management">
    <div class="header">
      <h1>Chat Management</h1>
      <div class="header-actions">
        <div class="filters">
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="closed">Closed</option>
          </select>
          <select v-model="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <option value="general">General</option>
            <option value="booking">Booking</option>
            <option value="support">Support</option>
            <option value="feedback">Feedback</option>
          </select>
          <select v-model="priorityFilter" class="filter-select">
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>
        <div class="search-container">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search chats..."
            class="search-input"
          >
        </div>
      </div>
    </div>

    <div class="stats-cards">
      <div class="stat-card">
        <h3>Active Chats</h3>
        <div class="stat-value">{{ stats.activeChats }}</div>
        <div class="stat-trend" :class="{ up: stats.activeChatsChange > 0 }">
          {{ formatTrend(stats.activeChatsChange) }}
        </div>
      </div>
      <div class="stat-card">
        <h3>Avg Response Time</h3>
        <div class="stat-value">{{ formatTime(stats.avgResponseTime) }}</div>
        <div class="stat-trend" :class="{ up: stats.responseTimeChange > 0 }">
          {{ formatTrend(stats.responseTimeChange) }}
        </div>
      </div>
      <div class="stat-card">
        <h3>Customer Satisfaction</h3>
        <div class="stat-value">{{ formatRating(stats.satisfaction) }}</div>
        <div class="stat-trend" :class="{ up: stats.satisfactionChange > 0 }">
          {{ formatTrend(stats.satisfactionChange) }}
        </div>
      </div>
      <div class="stat-card">
        <h3>Resolution Rate</h3>
        <div class="stat-value">{{ formatPercentage(stats.resolutionRate) }}</div>
        <div class="stat-trend" :class="{ up: stats.resolutionRateChange > 0 }">
          {{ formatTrend(stats.resolutionRateChange) }}
        </div>
      </div>
    </div>

    <div class="chats-container">
      <div class="chats-header">
        <h2>Chat Sessions</h2>
        <button @click="exportChats" class="export-btn">
          <i class="fas fa-download"></i>
          Export Data
        </button>
      </div>

      <div v-if="loading" class="loading-state">
        <LoadingAnimation />
      </div>

      <div v-else-if="filteredChats.length === 0" class="no-results">
        No chats found matching your criteria
      </div>

      <div v-else class="chats-grid">
        <div v-for="chat in filteredChats" :key="chat._id" class="chat-card">
          <div class="chat-header">
            <div class="customer-info">
              <img :src="chat.customer.avatar" :alt="chat.customer.name" class="avatar">
              <div class="info">
                <h4>{{ chat.customer.name }}</h4>
                <span class="email">{{ chat.customer.email }}</span>
              </div>
            </div>
            <div class="chat-meta">
              <span :class="['status-badge', chat.status]">{{ chat.status }}</span>
              <span :class="['priority-badge', chat.priority]">{{ chat.priority }}</span>
            </div>
          </div>

          <div class="chat-content">
            <div class="subject">{{ chat.subject }}</div>
            <div class="category">{{ chat.category }}</div>
            <div class="last-message">
              {{ chat.lastMessage?.content || 'No messages yet' }}
            </div>
          </div>

          <div class="chat-footer">
            <div class="agent-info" v-if="chat.agent">
              <img :src="chat.agent.avatar" :alt="chat.agent.name" class="avatar small">
              <span>{{ chat.agent.name }}</span>
            </div>
            <div class="timestamps">
              <span>Started: {{ formatDate(chat.createdAt) }}</span>
              <span>Last Activity: {{ formatDate(chat.lastActivity) }}</span>
            </div>
          </div>

          <div class="chat-actions">
            <button @click="viewChat(chat)" class="action-btn primary">
              View Chat
            </button>
            <button 
              v-if="chat.status !== 'closed'"
              @click="assignAgent(chat)"
              class="action-btn"
              :disabled="chat.agent"
            >
              {{ chat.agent ? 'Assigned' : 'Assign Agent' }}
            </button>
            <button
              v-if="chat.status === 'active'"
              @click="transferChat(chat)"
              class="action-btn"
            >
              Transfer
            </button>
            <button
              v-if="chat.status !== 'closed'"
              @click="closeChat(chat)"
              class="action-btn danger"
            >
              Close
            </button>
          </div>
        </div>
      </div>

      <div class="pagination">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="page-btn"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="page-btn"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <TransferChatModal
      v-if="showTransferModal"
      :chat="selectedChat"
      @close="showTransferModal = false"
      @transfer="handleTransfer"
    />

    <CloseChatModal
      v-if="showCloseModal"
      @close="showCloseModal = false"
      @confirm="handleClose"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';
import LoadingAnimation from '@/components/ui/LoadingAnimation.vue';
import TransferChatModal from '@/components/chat/TransferChatModal.vue';
import CloseChatModal from '@/components/chat/CloseChatModal.vue';

export default {
  name: 'ChatManagement',
  components: {
    LoadingAnimation,
    TransferChatModal,
    CloseChatModal
  },
  setup() {
    const router = useRouter();
    const { showNotification } = useNotifications();

    // State
    const loading = ref(true);
    const chats = ref([]);
    const stats = ref({
      activeChats: 0,
      activeChatsChange: 0,
      avgResponseTime: 0,
      responseTimeChange: 0,
      satisfaction: 0,
      satisfactionChange: 0,
      resolutionRate: 0,
      resolutionRateChange: 0
    });
    const statusFilter = ref('');
    const categoryFilter = ref('');
    const priorityFilter = ref('');
    const searchQuery = ref('');
    const currentPage = ref(1);
    const itemsPerPage = 12;
    const showTransferModal = ref(false);
    const showCloseModal = ref(false);
    const selectedChat = ref(null);

    // Computed
    const filteredChats = computed(() => {
      let filtered = chats.value;

      if (statusFilter.value) {
        filtered = filtered.filter(chat => chat.status === statusFilter.value);
      }

      if (categoryFilter.value) {
        filtered = filtered.filter(chat => chat.category === categoryFilter.value);
      }

      if (priorityFilter.value) {
        filtered = filtered.filter(chat => chat.priority === priorityFilter.value);
      }

      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(chat =>
          chat.customer.name.toLowerCase().includes(query) ||
          chat.customer.email.toLowerCase().includes(query) ||
          chat.subject.toLowerCase().includes(query) ||
          (chat.agent && chat.agent.name.toLowerCase().includes(query))
        );
      }

      return filtered;
    });

    const totalPages = computed(() => 
      Math.ceil(filteredChats.value.length / itemsPerPage)
    );

    const paginatedChats = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      return filteredChats.value.slice(start, end);
    });

    // Methods
    const loadChats = async () => {
      try {
        const response = await fetch('/api/chats');
        const data = await response.json();
        chats.value = data.chats;
      } catch (error) {
        showNotification('Error loading chats', 'error');
      } finally {
        loading.value = false;
      }
    };

    const loadStats = async () => {
      try {
        const response = await fetch('/api/chats/stats');
        const data = await response.json();
        stats.value = data.stats;
      } catch (error) {
        showNotification('Error loading statistics', 'error');
      }
    };

    const viewChat = (chat) => {
      router.push(`/admin/chats/${chat._id}`);
    };

    const assignAgent = async (chat) => {
      try {
        const response = await fetch(`/api/chats/${chat._id}/assign`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });

        if (!response.ok) throw new Error('Failed to assign agent');

        showNotification('Agent assigned successfully', 'success');
        await loadChats();
      } catch (error) {
        showNotification('Error assigning agent', 'error');
      }
    };

    const transferChat = (chat) => {
      selectedChat.value = chat;
      showTransferModal.value = true;
    };

    const handleTransfer = async (data) => {
      try {
        const response = await fetch(`/api/chats/${selectedChat.value._id}/transfer`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('Failed to transfer chat');

        showNotification('Chat transferred successfully', 'success');
        showTransferModal.value = false;
        await loadChats();
      } catch (error) {
        showNotification('Error transferring chat', 'error');
      }
    };

    const closeChat = (chat) => {
      selectedChat.value = chat;
      showCloseModal.value = true;
    };

    const handleClose = async (data) => {
      try {
        const response = await fetch(`/api/chats/${selectedChat.value._id}/close`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('Failed to close chat');

        showNotification('Chat closed successfully', 'success');
        showCloseModal.value = false;
        await loadChats();
      } catch (error) {
        showNotification('Error closing chat', 'error');
      }
    };

    const exportChats = async () => {
      try {
        const response = await fetch('/api/chats/export');
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `chats-export-${new Date().toISOString()}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      } catch (error) {
        showNotification('Error exporting chats', 'error');
      }
    };

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
      }
    };

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
      }
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleString();
    };

    const formatTime = (ms) => {
      const minutes = Math.floor(ms / 60000);
      return minutes < 1 ? '< 1 min' : `${minutes} min`;
    };

    const formatRating = (rating) => {
      return rating.toFixed(1);
    };

    const formatPercentage = (value) => {
      return `${(value * 100).toFixed(1)}%`;
    };

    const formatTrend = (value) => {
      const prefix = value > 0 ? '+' : '';
      return `${prefix}${value.toFixed(1)}%`;
    };

    // Watchers
    watch([statusFilter, categoryFilter, priorityFilter, searchQuery], () => {
      currentPage.value = 1;
    });

    // Lifecycle
    onMounted(() => {
      loadChats();
      loadStats();
    });

    return {
      loading,
      chats,
      stats,
      statusFilter,
      categoryFilter,
      priorityFilter,
      searchQuery,
      currentPage,
      totalPages,
      showTransferModal,
      showCloseModal,
      selectedChat,
      filteredChats,
      paginatedChats,
      viewChat,
      assignAgent,
      transferChat,
      handleTransfer,
      closeChat,
      handleClose,
      exportChats,
      prevPage,
      nextPage,
      formatDate,
      formatTime,
      formatRating,
      formatPercentage,
      formatTrend
    };
  }
};
</script>

<style scoped>
.chat-management {
  padding: 24px;
}

.header {
  margin-bottom: 24px;
}

.header h1 {
  margin: 0 0 16px 0;
  font-size: 24px;
  color: #111827;
}

.header-actions {
  display: flex;
  gap: 16px;
  align-items: center;
}

.filters {
  display: flex;
  gap: 8px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 14px;
}

.search-container {
  flex: 1;
  max-width: 300px;
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 14px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
  margin: 0 0 8px 0;
  font-size: 14px;
  color: #6B7280;
}

.stat-value {
  font-size: 24px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 4px;
}

.stat-trend {
  font-size: 14px;
  color: #EF4444;
}

.stat-trend.up {
  color: #10B981;
}

.chats-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 16px;
}

.chats-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.chats-header h2 {
  margin: 0;
  font-size: 18px;
  color: #111827;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #F3F4F6;
  border: none;
  border-radius: 6px;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.export-btn:hover {
  background: #E5E7EB;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.no-results {
  text-align: center;
  padding: 32px;
  color: #6B7280;
  font-style: italic;
}

.chats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.chat-card {
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 16px;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.customer-info {
  display: flex;
  align-items: center;
  gap: 8px;
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

.info h4 {
  margin: 0;
  font-size: 14px;
  color: #111827;
}

.email {
  font-size: 12px;
  color: #6B7280;
}

.chat-meta {
  display: flex;
  gap: 8px;
}

.status-badge,
.priority-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.pending {
  background: #FEF3C7;
  color: #92400E;
}

.status-badge.active {
  background: #DCFCE7;
  color: #166534;
}

.status-badge.closed {
  background: #F3F4F6;
  color: #374151;
}

.priority-badge.low {
  background: #E0E7FF;
  color: #3730A3;
}

.priority-badge.medium {
  background: #FEF3C7;
  color: #92400E;
}

.priority-badge.high {
  background: #FEE2E2;
  color: #991B1B;
}

.chat-content {
  margin-bottom: 12px;
}

.subject {
  font-weight: 500;
  color: #111827;
  margin-bottom: 4px;
}

.category {
  font-size: 12px;
  color: #6B7280;
  margin-bottom: 8px;
}

.last-message {
  font-size: 14px;
  color: #374151;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-size: 12px;
  color: #6B7280;
}

.agent-info {
  display: flex;
  align-items: center;
  gap: 4px;
}

.timestamps {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.chat-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.action-btn.primary {
  background: #4F46E5;
  color: white;
}

.action-btn.primary:hover {
  background: #4338CA;
}

.action-btn:not(.primary):not(.danger) {
  background: #F3F4F6;
  color: #374151;
}

.action-btn:not(.primary):not(.danger):hover {
  background: #E5E7EB;
}

.action-btn.danger {
  background: #FEE2E2;
  color: #991B1B;
}

.action-btn.danger:hover {
  background: #FEE2E2;
}

.action-btn:disabled {
  background: #F3F4F6;
  color: #9CA3AF;
  cursor: not-allowed;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.page-btn {
  padding: 8px;
  border: none;
  border-radius: 6px;
  background: #F3F4F6;
  color: #374151;
  cursor: pointer;
  transition: background-color 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #E5E7EB;
}

.page-btn:disabled {
  background: #F3F4F6;
  color: #9CA3AF;
  cursor: not-allowed;
}

.page-info {
  font-size: 14px;
  color: #6B7280;
}

@media (max-width: 768px) {
  .chat-management {
    padding: 16px;
  }

  .header-actions {
    flex-direction: column;
    gap: 8px;
  }

  .filters {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 8px;
  }

  .search-container {
    width: 100%;
    max-width: none;
  }

  .chats-grid {
    grid-template-columns: 1fr;
  }
}
</style>