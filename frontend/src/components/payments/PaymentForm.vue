<template>
  <div class="payment-form">
    <h2>Payment Details</h2>
    
    <div class="payment-summary">
      <h3>Booking Summary</h3>
      <div class="summary-details">
        <div class="summary-item">
          <span>Package:</span>
          <span>{{ booking.package.name }}</span>
        </div>
        <div class="summary-item">
          <span>Event Date:</span>
          <span>{{ formatDate(booking.eventDate) }}</span>
        </div>
        <div class="summary-item">
          <span>Total Amount:</span>
          <span class="amount">₱{{ formatPrice(booking.totalAmount) }}</span>
        </div>
      </div>
    </div>

    <div class="payment-methods">
      <h3>Select Payment Method</h3>
      <div class="method-options">
        <div 
          class="method-option"
          :class="{ active: selectedMethod === 'gcash' }"
          @click="selectedMethod = 'gcash'"
        >
          <img src="@/assets/gcash-logo.png" alt="GCash" />
          <span>GCash</span>
        </div>
        <div 
          class="method-option"
          :class="{ active: selectedMethod === 'cash' }"
          @click="selectedMethod = 'cash'"
        >
          <i class="fas fa-money-bill-wave"></i>
          <span>Cash</span>
        </div>
      </div>
    </div>

    <!-- GCash Payment Form -->
    <div v-if="selectedMethod === 'gcash'" class="gcash-form">
      <div class="form-group">
        <label>GCash Number</label>
        <input 
          type="text" 
          v-model="gcashNumber"
          placeholder="Enter GCash number"
          maxlength="11"
        />
      </div>
      <div class="form-group">
        <label>Account Name</label>
        <input 
          type="text" 
          v-model="gcashName"
          placeholder="Enter account name"
        />
      </div>
    </div>

    <!-- Cash Payment Form -->
    <div v-if="selectedMethod === 'cash'" class="cash-form">
      <p class="cash-instructions">
        Please prepare the exact amount for payment at our office.
        A receipt will be provided upon payment.
      </p>
    </div>

    <div class="payment-actions">
      <button 
        class="btn-pay"
        :disabled="!isFormValid || isProcessing"
        @click="processPayment"
      >
        <i class="fas fa-spinner fa-spin" v-if="isProcessing"></i>
        {{ isProcessing ? 'Processing...' : 'Proceed to Payment' }}
      </button>
      <button class="btn-cancel" @click="$emit('cancel')">Cancel</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useApi } from '@/composables/useApi';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['success', 'cancel']);
const { showNotification } = useNotifications();
const { post } = useApi();

const selectedMethod = ref('gcash');
const gcashNumber = ref('');
const gcashName = ref('');
const isProcessing = ref(false);

const isFormValid = computed(() => {
  if (selectedMethod.value === 'gcash') {
    return gcashNumber.value.length === 11 && gcashName.value.length > 0;
  }
  return true;
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

const processPayment = async () => {
  try {
    isProcessing.value = true;
    
    const paymentData = {
      bookingId: props.booking.id,
      method: selectedMethod.value,
      amount: props.booking.totalAmount,
      ...(selectedMethod.value === 'gcash' && {
        gcashNumber: gcashNumber.value,
        gcashName: gcashName.value
      })
    };

    const response = await post('/payments', paymentData);
    
    showNotification({
      type: 'success',
      message: 'Payment processed successfully!'
    });

    emit('success', response.data);
  } catch (error) {
    showNotification({
      type: 'error',
      message: error.message || 'Failed to process payment'
    });
  } finally {
    isProcessing.value = false;
  }
};
</script>

<style scoped>
.payment-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

h2 {
  text-align: center;
  color: var(--text-color);
  margin-bottom: 2rem;
}

.payment-summary {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.summary-details {
  margin-top: 1rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.amount {
  font-weight: 600;
  color: var(--primary-color);
}

.payment-methods {
  margin-bottom: 2rem;
}

.method-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.method-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  border: 2px solid var(--border-color);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.method-option img {
  height: 40px;
  object-fit: contain;
}

.method-option i {
  font-size: 2rem;
  color: var(--text-color);
}

.method-option.active {
  border-color: var(--primary-color);
  background: var(--primary-color-light);
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
}

.cash-instructions {
  padding: 1rem;
  background: var(--warning-color-light);
  border-radius: 8px;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.payment-actions {
  display: flex;
  gap: 1rem;
}

.btn-pay, .btn-cancel {
  flex: 1;
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-pay {
  background: var(--primary-color);
  color: white;
}

.btn-pay:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-cancel {
  background: var(--danger-color);
  color: white;
}

@media (max-width: 768px) {
  .payment-form {
    padding: 1rem;
  }

  .method-options {
    grid-template-columns: 1fr;
  }

  .payment-actions {
    flex-direction: column;
  }
}
</style> 