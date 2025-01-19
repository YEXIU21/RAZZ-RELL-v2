<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h3>Transfer Chat</h3>
        <button @click="close" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="loading-state">
          <LoadingAnimation />
        </div>
        <template v-else>
          <div class="search-container">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search agents..."
              class="search-input"
            >
          </div>

          <div class="agents-list">
            <div v-if="filteredAgents.length === 0" class="no-results">
              No agents found
            </div>
            <button
              v-for="agent in filteredAgents"
              :key="agent._id"
              @click="selectAgent(agent)"
              :class="['agent-item', { selected: selectedAgent?._id === agent._id }]"
            >
              <img :src="agent.avatar" :alt="agent.name" class="agent-avatar">
              <div class="agent-info">
                <h4>{{ agent.name }}</h4>
                <span :class="['status', { online: agent.isOnline }]">
                  {{ agent.isOnline ? 'Online' : 'Offline' }}
                </span>
              </div>
              <div class="agent-stats">
                <div class="stat">
                  <span class="stat-label">Active Chats:</span>
                  <span class="stat-value">{{ agent.activeChats }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Avg. Response:</span>
                  <span class="stat-value">{{ formatResponseTime(agent.avgResponseTime) }}</span>
                </div>
              </div>
            </button>
          </div>

          <div class="transfer-note">
            <label for="transferNote">Transfer Note (optional):</label>
            <textarea
              id="transferNote"
              v-model="transferNote"
              placeholder="Add a note for the new agent..."
              rows="3"
            ></textarea>
          </div>
        </template>
      </div>

      <div class="modal-footer">
        <button @click="close" class="cancel-btn">
          Cancel
        </button>
        <button
          @click="transfer"
          :disabled="!canTransfer || transferring"
          class="transfer-btn"
        >
          {{ transferring ? 'Transferring...' : 'Transfer Chat' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import LoadingAnimation from '@/components/ui/LoadingAnimation.vue';

export default {
  name: 'TransferChatModal',
  components: {
    LoadingAnimation
  },
  props: {
    chat: {
      type: Object,
      required: true
    }
  },
  setup(props, { emit }) {
    const { showNotification } = useNotifications();
    const loading = ref(true);
    const transferring = ref(false);
    const agents = ref([]);
    const selectedAgent = ref(null);
    const searchQuery = ref('');
    const transferNote = ref('');

    const filteredAgents = computed(() => {
      if (!searchQuery.value) return agents.value;
      const query = searchQuery.value.toLowerCase();
      return agents.value.filter(agent => 
        agent.name.toLowerCase().includes(query) ||
        agent.email.toLowerCase().includes(query)
      );
    });

    const canTransfer = computed(() => {
      return selectedAgent.value && !transferring.value;
    });

    const loadAgents = async () => {
      try {
        const response = await fetch('/api/agents/available');
        const data = await response.json();
        agents.value = data.agents.filter(agent => 
          agent._id !== props.chat.agent?._id
        );
      } catch (error) {
        showNotification('Error loading agents', 'error');
      } finally {
        loading.value = false;
      }
    };

    const selectAgent = (agent) => {
      selectedAgent.value = agent;
    };

    const formatResponseTime = (time) => {
      if (!time) return 'N/A';
      const minutes = Math.floor(time / 60000);
      if (minutes < 1) return '< 1 min';
      return `${minutes} min`;
    };

    const transfer = async () => {
      if (!canTransfer.value) return;
      transferring.value = true;

      try {
        const response = await fetch(`/api/chat/${props.chat._id}/transfer`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            newAgentId: selectedAgent.value._id,
            note: transferNote.value
          })
        });

        if (!response.ok) throw new Error('Transfer failed');

        showNotification('Chat transferred successfully', 'success');
        emit('transfer', {
          agent: selectedAgent.value,
          note: transferNote.value
        });
        close();
      } catch (error) {
        showNotification('Failed to transfer chat', 'error');
      } finally {
        transferring.value = false;
      }
    };

    const close = () => {
      emit('close');
    };

    onMounted(loadAgents);

    return {
      loading,
      transferring,
      agents,
      selectedAgent,
      searchQuery,
      transferNote,
      filteredAgents,
      canTransfer,
      selectAgent,
      formatResponseTime,
      transfer,
      close
    };
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
}

.modal-container {
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  background: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: #111827;
}

.close-btn {
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  padding: 4px;
}

.modal-body {
  padding: 16px;
  overflow-y: auto;
  flex: 1;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.search-container {
  margin-bottom: 16px;
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 14px;
}

.agents-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
  max-height: 300px;
  overflow-y: auto;
}

.no-results {
  text-align: center;
  padding: 16px;
  color: #6B7280;
  font-style: italic;
}

.agent-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  background: none;
  cursor: pointer;
  transition: all 0.2s;
}

.agent-item:hover {
  background: #F3F4F6;
}

.agent-item.selected {
  border-color: #4F46E5;
  background: #EEF2FF;
}

.agent-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.agent-info {
  flex: 1;
  text-align: left;
}

.agent-info h4 {
  margin: 0;
  font-size: 14px;
  color: #111827;
}

.status {
  font-size: 12px;
  color: #6B7280;
}

.status.online {
  color: #10B981;
}

.agent-stats {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  color: #6B7280;
}

.stat {
  display: flex;
  gap: 4px;
}

.stat-label {
  color: #6B7280;
}

.stat-value {
  color: #111827;
  font-weight: 500;
}

.transfer-note {
  margin-top: 16px;
}

.transfer-note label {
  display: block;
  margin-bottom: 4px;
  font-size: 14px;
  color: #374151;
}

.transfer-note textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  resize: vertical;
  font-family: inherit;
}

.modal-footer {
  padding: 16px;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.cancel-btn,
.transfer-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-btn {
  background: #F3F4F6;
  color: #374151;
}

.cancel-btn:hover {
  background: #E5E7EB;
}

.transfer-btn {
  background: #4F46E5;
  color: white;
}

.transfer-btn:hover:not(:disabled) {
  background: #4338CA;
}

.transfer-btn:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-container {
    width: 100%;
    height: 100%;
    max-height: none;
    border-radius: 0;
  }

  .agents-list {
    max-height: none;
  }
}
</style> 