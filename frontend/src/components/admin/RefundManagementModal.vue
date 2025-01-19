<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Refund Request</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="booking-details">
          <h3>Booking Details</h3>
          <div class="details-grid">
            <div class="detail-item">
              <span class="label">Customer:</span>
              <span>{{ booking.customerName }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Event Type:</span>
              <span>{{ booking.eventType }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Event Date:</span>
              <span>{{ formatDate(booking.eventDate) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Amount Paid:</span>
              <span class="amount">₱{{ formatPrice(booking.amountPaid) }}</span>
            </div>
          </div>
        </div>

        <div class="refund-form">
          <div class="form-group">
            <label>Refund Amount</label>
            <div class="amount-input">
              <span class="currency">₱</span>
              <input 
                type="number"
                v-model="refundAmount"
                :max="booking.amountPaid"
                step="0.01"
              />
            </div>
            <div class="amount-hint">
              Maximum refund amount: ₱{{ formatPrice(booking.amountPaid) }}
            </div>
          </div>

          <div class="form-group">
            <label>Refund Reason</label>
            <select v-model="refundReason">
              <option value="">Select a reason</option>
              <option value="cancellation">Event Cancellation</option>
              <option value="rescheduling">Event Rescheduling</option>
              <option value="double_payment">Double Payment</option>
              <option value="service_issue">Service Issue</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-group" v-if="refundReason === 'other'">
            <label>Specify Reason</label>
            <textarea 
              v-model="otherReason"
              placeholder="Please provide detailed explanation"
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Admin Notes</label>
            <textarea 
              v-model="adminNotes"
              placeholder="Add internal notes about this refund"
              rows="3"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Refund Method</label>
            <div class="method-options">
              <label class="method-option">
                <input 
                  type="radio" 
                  v-model="refundMethod"
                  value="original"
                />
                <span class="method-label">
                  <i class="fas fa-undo"></i>
                  Original Payment Method
                </span>
              </label>
              <label class="method-option">
                <input 
                  type="radio" 
                  v-model="refundMethod"
                  value="manual"
                />
                <span class="method-label">
                  <i class="fas fa-hand-holding-usd"></i>
                  Manual Refund
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          class="btn-approve"
          :disabled="!isValid || isProcessing"
          @click="processRefund(true)"
        >
          <i class="fas fa-spinner fa-spin" v-if="isProcessing"></i>
          {{ isProcessing ? 'Processing...' : 'Approve & Process Refund' }}
        </button>
        <button 
          class="btn-reject"
          :disabled="isProcessing"
          @click="processRefund(false)"
        >
          Reject Refund
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'refund-processed']);
const { post } = useApi();
const { showNotification } = useNotifications();

const refundAmount = ref(props.booking.amountPaid);
const refundReason = ref('');
const otherReason = ref('');
const adminNotes = ref('');
const refundMethod = ref('original');
const isProcessing = ref(false);

const isValid = computed(() => {
  return refundAmount.value > 0 && 
         refundAmount.value <= props.booking.amountPaid &&
         refundReason.value &&
         (refundReason.value !== 'other' || otherReason.value) &&
         refundMethod.value;
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatPrice = (price) => {
  return price.toLocaleString('en-PH');
};

const processRefund = async (approved) => {
  try {
    isProcessing.value = true;

    const refundData = {
      bookingId: props.booking.id,
      amount: refundAmount.value,
      reason: refundReason.value === 'other' ? otherReason.value : refundReason.value,
      method: refundMethod.value,
      adminNotes: adminNotes.value,
      status: approved ? 'approved' : 'rejected'
    };

    const response = await post('/refunds', refundData);

    showNotification({
      type: 'success',
      message: `Refund request ${approved ? 'approved' : 'rejected'} successfully`
    });

    emit('refund-processed', response.data);
    emit('close');
  } catch (error) {
    showNotification({
      type: 'error',
      message: error.message || 'Failed to process refund'
    });
  } finally {
    isProcessing.value = false;
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
  width: 90%;
  max-width: 600px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: var(--hover-color);
  color: var(--text-color);
}

.modal-body {
  padding: 1.5rem;
}

.booking-details {
  background: var(--background-color);
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.label {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.amount {
  color: var(--primary-color);
  font-weight: 600;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
  font-weight: 500;
}

.amount-input {
  position: relative;
  display: flex;
  align-items: center;
}

.currency {
  position: absolute;
  left: 1rem;
  color: var(--text-muted);
}

input[type="number"] {
  width: 100%;
  padding: 0.75rem;
  padding-left: 2rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

.amount-hint {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

select, textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

textarea {
  resize: vertical;
  min-height: 80px;
}

.method-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.method-option {
  cursor: pointer;
}

.method-option input[type="radio"] {
  display: none;
}

.method-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.method-label i {
  font-size: 1.25rem;
  color: var(--text-muted);
}

.method-option input[type="radio"]:checked + .method-label {
  border-color: var(--primary-color);
  background: var(--primary-color-light);
}

.method-option input[type="radio"]:checked + .method-label i {
  color: var(--primary-color);
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  gap: 1rem;
}

.btn-approve, .btn-reject {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-approve {
  background: var(--primary-color);
  color: white;
}

.btn-approve:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-reject {
  background: var(--danger-color);
  color: white;
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .method-options {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column;
  }
}
</style> 