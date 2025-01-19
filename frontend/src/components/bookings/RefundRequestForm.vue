<template>
  <div class="refund-request-form">
    <div class="form-header">
      <h3>Request a Refund</h3>
      <p class="subtitle">Please provide the details for your refund request</p>
    </div>

    <form @submit.prevent="submitRequest" class="form-content">
      <div class="booking-summary">
        <h4>Booking Summary</h4>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="label">Event Type</span>
            <span class="value">{{ booking.eventType }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Event Date</span>
            <span class="value">{{ formatDate(booking.eventDate) }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Package</span>
            <span class="value">{{ booking.packageName }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Amount Paid</span>
            <span class="value amount">₱{{ formatPrice(booking.amountPaid) }}</span>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Refund Amount</label>
        <div class="amount-input">
          <span class="currency">₱</span>
          <input
            type="number"
            v-model="refundAmount"
            :max="booking.amountPaid"
            step="0.01"
            required
            :class="{ 'error': errors.amount }"
          />
        </div>
        <div class="amount-hint" :class="{ 'error': errors.amount }">
          {{ errors.amount || `Maximum refund amount: ₱${formatPrice(booking.amountPaid)}` }}
        </div>
      </div>

      <div class="form-group">
        <label>Reason for Refund</label>
        <select 
          v-model="reason"
          required
          :class="{ 'error': errors.reason }"
        >
          <option value="">Select a reason</option>
          <option value="cancellation">Event Cancellation</option>
          <option value="rescheduling">Event Rescheduling</option>
          <option value="double_payment">Double Payment</option>
          <option value="service_issue">Service Issue</option>
          <option value="other">Other</option>
        </select>
        <span class="error-message" v-if="errors.reason">{{ errors.reason }}</span>
      </div>

      <div class="form-group" v-if="reason === 'other'">
        <label>Please Explain</label>
        <textarea
          v-model="explanation"
          rows="4"
          placeholder="Please provide detailed explanation for your refund request"
          required
          :class="{ 'error': errors.explanation }"
        ></textarea>
        <span class="error-message" v-if="errors.explanation">{{ errors.explanation }}</span>
      </div>

      <div class="form-group">
        <label>Preferred Refund Method</label>
        <div class="method-options">
          <label class="method-option">
            <input 
              type="radio"
              v-model="refundMethod"
              value="original"
              required
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
              value="bank"
              required
            />
            <span class="method-label">
              <i class="fas fa-university"></i>
              Bank Transfer
            </span>
          </label>
          <label class="method-option">
            <input 
              type="radio"
              v-model="refundMethod"
              value="gcash"
              required
            />
            <span class="method-label">
              <i class="fas fa-mobile-alt"></i>
              GCash
            </span>
          </label>
        </div>
        <span class="error-message" v-if="errors.refundMethod">{{ errors.refundMethod }}</span>
      </div>

      <div class="form-group" v-if="refundMethod === 'bank'">
        <label>Bank Account Details</label>
        <div class="bank-details">
          <input
            type="text"
            v-model="bankDetails.accountName"
            placeholder="Account Name"
            required
            :class="{ 'error': errors.bankAccountName }"
          />
          <input
            type="text"
            v-model="bankDetails.accountNumber"
            placeholder="Account Number"
            required
            :class="{ 'error': errors.bankAccountNumber }"
          />
          <select
            v-model="bankDetails.bankName"
            required
            :class="{ 'error': errors.bankName }"
          >
            <option value="">Select Bank</option>
            <option value="bdo">BDO</option>
            <option value="bpi">BPI</option>
            <option value="metrobank">Metrobank</option>
            <option value="unionbank">UnionBank</option>
            <option value="securitybank">Security Bank</option>
          </select>
        </div>
      </div>

      <div class="form-group" v-if="refundMethod === 'gcash'">
        <label>GCash Number</label>
        <input
          type="tel"
          v-model="gcashNumber"
          placeholder="09XX XXX XXXX"
          pattern="[0-9]{11}"
          required
          :class="{ 'error': errors.gcashNumber }"
        />
        <span class="error-message" v-if="errors.gcashNumber">{{ errors.gcashNumber }}</span>
      </div>

      <div class="form-actions">
        <button 
          type="button" 
          class="btn-cancel"
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button 
          type="submit"
          class="btn-submit"
          :disabled="isSubmitting"
        >
          <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
          {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';
import { useValidation } from '@/composables/useValidation';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['cancel', 'submitted']);

const { post } = useApi();
const { showNotification } = useNotifications();
const { validateRequired, validateNumber, validatePhone } = useValidation();

const refundAmount = ref(props.booking.amountPaid);
const reason = ref('');
const explanation = ref('');
const refundMethod = ref('');
const bankDetails = ref({
  accountName: '',
  accountNumber: '',
  bankName: ''
});
const gcashNumber = ref('');
const isSubmitting = ref(false);
const errors = ref({});

const validateForm = () => {
  const newErrors = {};

  // Validate amount
  if (!validateNumber(refundAmount.value, 0, props.booking.amountPaid)) {
    newErrors.amount = `Amount must be between 0 and ₱${formatPrice(props.booking.amountPaid)}`;
  }

  // Validate reason
  if (!validateRequired(reason.value)) {
    newErrors.reason = 'Please select a reason';
  }

  // Validate explanation for 'other' reason
  if (reason.value === 'other' && !validateRequired(explanation.value)) {
    newErrors.explanation = 'Please provide an explanation';
  }

  // Validate refund method
  if (!validateRequired(refundMethod.value)) {
    newErrors.refundMethod = 'Please select a refund method';
  }

  // Validate bank details
  if (refundMethod.value === 'bank') {
    if (!validateRequired(bankDetails.value.accountName)) {
      newErrors.bankAccountName = 'Account name is required';
    }
    if (!validateRequired(bankDetails.value.accountNumber)) {
      newErrors.bankAccountNumber = 'Account number is required';
    }
    if (!validateRequired(bankDetails.value.bankName)) {
      newErrors.bankName = 'Bank name is required';
    }
  }

  // Validate GCash number
  if (refundMethod.value === 'gcash' && !validatePhone(gcashNumber.value)) {
    newErrors.gcashNumber = 'Please enter a valid GCash number';
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

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

const submitRequest = async () => {
  if (!validateForm()) return;

  try {
    isSubmitting.value = true;

    const requestData = {
      bookingId: props.booking.id,
      amount: refundAmount.value,
      reason: reason.value === 'other' ? explanation.value : reason.value,
      refundMethod: refundMethod.value,
      ...(refundMethod.value === 'bank' ? { bankDetails: bankDetails.value } : {}),
      ...(refundMethod.value === 'gcash' ? { gcashNumber: gcashNumber.value } : {})
    };

    const response = await post('/refunds/request', requestData);

    showNotification({
      type: 'success',
      message: 'Refund request submitted successfully'
    });

    emit('submitted', response.data);
  } catch (error) {
    showNotification({
      type: 'error',
      message: error.message || 'Failed to submit refund request'
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.refund-request-form {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.form-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.form-header h3 {
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.subtitle {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.form-content {
  padding: 1.5rem;
}

.booking-summary {
  background: var(--background-color);
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.booking-summary h4 {
  margin-bottom: 1rem;
  color: var(--text-color);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.label {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.value {
  font-weight: 500;
  color: var(--text-color);
}

.value.amount {
  color: var(--primary-color);
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

input[type="number"],
input[type="text"],
input[type="tel"],
select,
textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
  transition: border-color 0.2s ease;
}

input[type="number"] {
  padding-left: 2rem;
}

.amount-hint {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

.amount-hint.error {
  color: var(--danger-color);
}

textarea {
  resize: vertical;
  min-height: 100px;
}

.method-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 0.5rem;
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

.bank-details {
  display: grid;
  gap: 1rem;
}

.error {
  border-color: var(--danger-color);
}

.error-message {
  display: block;
  color: var(--danger-color);
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel {
  background: var(--secondary-color);
  color: white;
}

.btn-submit {
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .summary-grid,
  .method-options {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style> 