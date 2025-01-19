<template>
  <div class="chat-card" :class="{ 'active': isActive }">
    <div class="chat-header">
      <div class="user-info">
        <img :src="chat.user.avatar || defaultAvatar" :alt="chat.user.name" class="user-avatar">
        <div class="user-details">
          <h3>{{ chat.user.name }}</h3>
          <span class="last-active">{{ formatLastActive(chat.last_message_at) }}</span>
        </div>
      </div>
      <div class="chat-actions">
        <button 
          v-if="chat.status !== 'closed'"
          @click.stop="showTransferModal = true" 
          class="action-btn transfer"
          title="Transfer Chat"
        >
          <i class="fas fa-exchange-alt"></i>
        </button>
        <button 
          v-if="chat.status !== 'closed'"
          @click.stop="showCloseModal = true"
          class="action-btn close"
          title="Close Chat"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <div class="chat-preview">
      <p class="last-message">
        {{ chat.last_message?.content || 'No messages yet' }}
      </p>
      <div class="chat-meta">
        <span class="message-time">{{ formatTime(chat.last_message_at) }}</span>
        <span v-if="chat.unread_count" class="unread-badge">{{ chat.unread_count }}</span>
      </div>
    </div>

    <!-- Transfer Chat Modal -->
    <div v-if="showTransferModal" class="modal-container">
      <div class="modal-backdrop" @click="showTransferModal = false" />
      <div class="modal-content">
        <h3 class="modal-title">Transfer Chat</h3>

        <div class="modal-body">
          <div class="form-group">
            <label for="staff">Select Staff Member</label>
            <select 
              id="staff" 
              v-model="selectedStaff"
              class="form-select"
            >
              <option value="">Select a staff member</option>
              <option 
                v-for="staff in staffMembers" 
                :key="staff.id" 
                :value="staff.id"
              >
                {{ staff.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label for="transferNote">Transfer Note (Optional)</label>
            <textarea
              id="transferNote"
              v-model="transferNote"
              placeholder="Add a note for the staff member..."
              class="form-textarea"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            @click="showTransferModal = false"
            class="btn btn-secondary"
          >
            Cancel
          </button>
          <button 
            @click="handleTransfer"
            class="btn btn-primary"
            :disabled="!selectedStaff"
          >
            Transfer Chat
          </button>
        </div>
      </div>
    </div>

    <!-- Close Chat Modal -->
    <div v-if="showCloseModal" class="modal-container">
      <div class="modal-backdrop" @click="showCloseModal = false" />
      <div class="modal-content">
        <h3 class="modal-title">Close Chat</h3>

        <div class="modal-body">
          <p>Are you sure you want to close this chat? This action cannot be undone.</p>
          <div class="form-group">
            <label for="closeNote">Closing Note (Optional)</label>
            <textarea
              id="closeNote"
              v-model="closeNote"
              placeholder="Add a closing note..."
              class="form-textarea"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            @click="showCloseModal = false"
            class="btn btn-secondary"
          >
            Cancel
          </button>
          <button 
            @click="handleClose"
            class="btn btn-danger"
          >
            Close Chat
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import defaultAvatar from '@/assets/default-profile.png';
import { formatDistanceToNow } from 'date-fns';

const props = defineProps({
  chat: {
    type: Object,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  },
  staffMembers: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['transfer', 'close']);

const showTransferModal = ref(false);
const showCloseModal = ref(false);
const selectedStaff = ref('');
const transferNote = ref('');
const closeNote = ref('');

const formatLastActive = (date) => {
  if (!date) return 'Never';
  return formatDistanceToNow(new Date(date), { addSuffix: true });
};

const formatTime = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const handleTransfer = async () => {
  if (!selectedStaff.value) return;
  
  emit('transfer', {
    chatId: props.chat.id,
    staffId: selectedStaff.value,
    note: transferNote.value
  });
  
  showTransferModal.value = false;
  selectedStaff.value = '';
  transferNote.value = '';
};

const handleClose = async () => {
  emit('close', {
    chatId: props.chat.id,
    note: closeNote.value
  });
  
  showCloseModal.value = false;
  closeNote.value = '';
};
</script>

<style scoped>
.chat-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid #E5E7EB;
}

.chat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.chat-card.active {
  border-color: #4F46E5;
  background: #F8FAFC;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1F2937;
}

.last-active {
  font-size: 0.875rem;
  color: #6B7280;
}

.chat-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn.transfer {
  color: #4F46E5;
}

.action-btn.transfer:hover {
  background: #EEF2FF;
}

.action-btn.close {
  color: #DC2626;
}

.action-btn.close:hover {
  background: #FEE2E2;
}

.chat-preview {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.last-message {
  margin: 0;
  font-size: 0.875rem;
  color: #4B5563;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.chat-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.message-time {
  font-size: 0.75rem;
  color: #6B7280;
}

.unread-badge {
  background: #4F46E5;
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Modal Styles */
.modal-container {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  z-index: 1;
}

.modal-title {
  margin: 0 0 1.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1F2937;
}

.modal-body {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #1F2937;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary {
  background: #F3F4F6;
  color: #374151;
}

.btn-secondary:hover {
  background: #E5E7EB;
}

.btn-primary {
  background: #4F46E5;
  color: white;
}

.btn-primary:hover {
  background: #4338CA;
}

.btn-primary:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

.btn-danger {
  background: #DC2626;
  color: white;
}

.btn-danger:hover {
  background: #B91C1C;
}
</style> 