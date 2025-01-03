<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h3>Close Chat</h3>
        <button @click="close" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <p class="confirmation-text">
          Are you sure you want to close this chat? This action cannot be undone.
        </p>

        <div class="reason-section">
          <label for="closeReason">Close Reason:</label>
          <select id="closeReason" v-model="selectedReason" class="reason-select">
            <option value="">Select a reason</option>
            <option value="resolved">Issue Resolved</option>
            <option value="customer_request">Customer Request</option>
            <option value="inactive">Customer Inactive</option>
            <option value="duplicate">Duplicate Chat</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div v-if="selectedReason === 'other'" class="custom-reason">
          <label for="customReason">Specify Reason:</label>
          <textarea
            id="customReason"
            v-model="customReason"
            placeholder="Enter the reason for closing the chat..."
            rows="3"
            class="custom-reason-input"
          ></textarea>
        </div>

        <div class="resolution-note">
          <label for="resolutionNote">Resolution Note (optional):</label>
          <textarea
            id="resolutionNote"
            v-model="resolutionNote"
            placeholder="Add any notes about how the issue was resolved..."
            rows="3"
            class="resolution-note-input"
          ></textarea>
        </div>

        <div class="feedback-prompt">
          <label class="feedback-label">
            <input
              type="checkbox"
              v-model="requestFeedback"
              class="feedback-checkbox"
            >
            Request customer feedback
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="close" class="cancel-btn">
          Cancel
        </button>
        <button
          @click="confirm"
          :disabled="!canClose || closing"
          class="close-chat-btn"
        >
          {{ closing ? 'Closing...' : 'Close Chat' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'CloseChatModal',
  emits: ['close', 'confirm'],
  setup(props, { emit }) {
    const selectedReason = ref('');
    const customReason = ref('');
    const resolutionNote = ref('');
    const requestFeedback = ref(true);
    const closing = ref(false);

    const canClose = computed(() => {
      if (!selectedReason.value) return false;
      if (selectedReason.value === 'other' && !customReason.value.trim()) return false;
      return true;
    });

    const close = () => {
      emit('close');
    };

    const confirm = () => {
      if (!canClose.value || closing.value) return;
      closing.value = true;

      const reason = selectedReason.value === 'other' 
        ? customReason.value 
        : selectedReason.value;

      emit('confirm', {
        reason,
        resolutionNote: resolutionNote.value,
        requestFeedback: requestFeedback.value
      });
    };

    return {
      selectedReason,
      customReason,
      resolutionNote,
      requestFeedback,
      closing,
      canClose,
      close,
      confirm
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
  max-width: 500px;
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
}

.confirmation-text {
  margin: 0 0 20px;
  color: #374151;
  font-size: 14px;
}

.reason-section {
  margin-bottom: 16px;
}

.reason-section label,
.custom-reason label,
.resolution-note label {
  display: block;
  margin-bottom: 4px;
  font-size: 14px;
  color: #374151;
}

.reason-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 14px;
  color: #111827;
  background-color: white;
}

.custom-reason {
  margin-bottom: 16px;
}

.custom-reason-input,
.resolution-note-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  resize: vertical;
  font-family: inherit;
  font-size: 14px;
}

.resolution-note {
  margin-bottom: 16px;
}

.feedback-prompt {
  margin-bottom: 16px;
}

.feedback-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
}

.feedback-checkbox {
  width: 16px;
  height: 16px;
  border: 1px solid #E5E7EB;
  border-radius: 4px;
  cursor: pointer;
}

.modal-footer {
  padding: 16px;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.cancel-btn,
.close-chat-btn {
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

.close-chat-btn {
  background: #EF4444;
  color: white;
}

.close-chat-btn:hover:not(:disabled) {
  background: #DC2626;
}

.close-chat-btn:disabled {
  background: #E5E7EB;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-container {
    width: 100%;
    height: 100%;
    border-radius: 0;
  }
}
</style> 