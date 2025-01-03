<template>
  <div class="payment-management">
    <div class="header">
      <h2>Payment Management</h2>
      <div class="filters">
        <div class="filter-group">
          <label>Status</label>
          <select v-model="filters.status">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Payment Method</label>
          <select v-model="filters.paymentMethod">
            <option value="">All</option>
            <option value="gcash">GCash</option>
            <option value="cash">Cash</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Date Range</label>
          <div class="date-inputs">
            <input 
              type="date" 
              v-model="filters.startDate"
              :max="filters.endDate || today"
            >
            <span>to</span>
            <input 
              type="date" 
              v-model="filters.endDate"
              :min="filters.startDate"
              :max="today"
            >
          </div>
        </div>

        <button class="apply-btn" @click="loadPayments">
          Apply Filters
        </button>
      </div>
    </div>

    <div class="payments-table">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Booking</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="payment in payments" :key="payment.id">
            <td>#{{ payment.id }}</td>
            <td>
              <div class="booking-info">
                <span class="booking-id">#{{ payment.booking.id }}</span>
                <span class="event-type">{{ payment.booking.event_type }}</span>
              </div>
            </td>
            <td>
              <div class="customer-info">
                <span class="name">{{ payment.booking.full_name }}</span>
                <span class="email">{{ payment.booking.email }}</span>
              </div>
            </td>
            <td>₱{{ formatPrice(payment.amount) }}</td>
            <td>
              <span class="payment-method" :class="payment.payment_method">
                {{ payment.payment_method === 'gcash' ? 'GCash' : 'Cash' }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="payment.status">
                {{ payment.status }}
              </span>
            </td>
            <td>{{ formatDate(payment.created_at) }}</td>
            <td>
              <div class="actions">
                <button 
                  v-if="payment.status === 'pending'"
                  class="action-btn approve"
                  @click="updateStatus(payment, 'completed')"
                >
                  Approve
                </button>
                <button 
                  v-if="payment.status === 'pending'"
                  class="action-btn reject"
                  @click="updateStatus(payment, 'failed')"
                >
                  Reject
                </button>
                <button 
                  class="action-btn view"
                  @click="viewDetails(payment)"
                >
                  View
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Payment Details Modal -->
    <div v-if="selectedPayment" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Payment Details</h3>
          <button class="close-btn" @click="selectedPayment = null">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="details-grid">
            <div class="detail-group">
              <h4>Payment Information</h4>
              <p><strong>ID:</strong> #{{ selectedPayment.id }}</p>
              <p><strong>Amount:</strong> ₱{{ formatPrice(selectedPayment.amount) }}</p>
              <p><strong>Method:</strong> {{ selectedPayment.payment_method === 'gcash' ? 'GCash' : 'Cash' }}</p>
              <p><strong>Status:</strong> {{ selectedPayment.status }}</p>
              <p v-if="selectedPayment.gcash_reference_number">
                <strong>GCash Reference:</strong> {{ selectedPayment.gcash_reference_number }}
              </p>
              <p v-if="selectedPayment.notes">
                <strong>Notes:</strong> {{ selectedPayment.notes }}
              </p>
            </div>

            <div class="detail-group">
              <h4>Booking Details</h4>
              <p><strong>Booking ID:</strong> #{{ selectedPayment.booking.id }}</p>
              <p><strong>Event Type:</strong> {{ selectedPayment.booking.event_type }}</p>
              <p><strong>Event Date:</strong> {{ formatDate(selectedPayment.booking.event_date) }}</p>
              <p><strong>Status:</strong> {{ selectedPayment.booking.status }}</p>
            </div>

            <div class="detail-group">
              <h4>Customer Information</h4>
              <p><strong>Name:</strong> {{ selectedPayment.booking.full_name }}</p>
              <p><strong>Email:</strong> {{ selectedPayment.booking.email }}</p>
              <p><strong>Phone:</strong> {{ selectedPayment.booking.phone }}</p>
            </div>
          </div>

          <!-- Receipt Image -->
          <div v-if="selectedPayment.receipt_image" class="receipt-section">
            <h4>Payment Receipt</h4>
            <div class="receipt-image">
              <img 
                :src="getReceiptUrl(selectedPayment.receipt_image)" 
                alt="Payment Receipt"
                @click="openImagePreview(selectedPayment.receipt_image)"
              >
            </div>
            <a 
              :href="getReceiptUrl(selectedPayment.receipt_image)" 
              download 
              class="download-receipt"
            >
              <i class="fas fa-download"></i>
              Download Receipt
            </a>
          </div>
        </div>

        <div v-if="selectedPayment.status === 'pending'" class="modal-footer">
          <button 
            class="action-btn approve"
            @click="updateStatus(selectedPayment, 'completed')"
          >
            Approve Payment
          </button>
          <button 
            class="action-btn reject"
            @click="updateStatus(selectedPayment, 'failed')"
          >
            Reject Payment
          </button>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div v-if="previewImage" class="image-preview-modal" @click="closeImagePreview">
      <div class="modal-content">
        <img :src="previewImage" alt="Preview">
        <button class="close-preview" @click="closeImagePreview">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// State
const payments = ref([]);
const selectedPayment = ref(null);
const previewImage = ref(null);
const filters = ref({
  status: '',
  paymentMethod: '',
  startDate: '',
  endDate: ''
});

// Computed
const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

// Methods
const loadPayments = async () => {
  try {
    const params = {
      status: filters.value.status,
      payment_method: filters.value.paymentMethod,
      start_date: filters.value.startDate,
      end_date: filters.value.endDate
    };

    const response = await axios.get('/api/payments', { params });
    payments.value = response.data.payments;
  } catch (error) {
    console.error('Error loading payments:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load payments'
    });
  }
};

const updateStatus = async (payment, newStatus) => {
  try {
    const result = await Swal.fire({
      title: `${newStatus === 'completed' ? 'Approve' : 'Reject'} Payment?`,
      text: `Are you sure you want to ${newStatus === 'completed' ? 'approve' : 'reject'} this payment?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: newStatus === 'completed' ? '#10B981' : '#EF4444',
      confirmButtonText: newStatus === 'completed' ? 'Yes, approve' : 'Yes, reject',
      cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
      const notes = await Swal.fire({
        title: 'Add Notes (Optional)',
        input: 'textarea',
        inputPlaceholder: 'Enter any notes about this decision...',
        showCancelButton: true,
        inputValidator: (value) => {
          if (value && value.length > 500) {
            return 'Notes cannot exceed 500 characters';
          }
        }
      });

      const response = await axios.put(`/api/payments/${payment.id}/status`, {
        status: newStatus,
        notes: notes.value
      });

      // Update the payment in the list
      const index = payments.value.findIndex(p => p.id === payment.id);
      if (index !== -1) {
        payments.value[index] = response.data.payment;
      }

      // Update selected payment if in modal
      if (selectedPayment.value?.id === payment.id) {
        selectedPayment.value = response.data.payment;
      }

      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: `Payment ${newStatus === 'completed' ? 'approved' : 'rejected'} successfully`
      });
    }
  } catch (error) {
    console.error('Error updating payment status:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update payment status'
    });
  }
};

const viewDetails = (payment) => {
  selectedPayment.value = payment;
};

const formatPrice = (price) => {
  return Number(price).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getReceiptUrl = (path) => {
  return `${import.meta.env.VITE_API_URL}/storage/${path}`;
};

const openImagePreview = (imagePath) => {
  previewImage.value = getReceiptUrl(imagePath);
};

const closeImagePreview = () => {
  previewImage.value = null;
};

// Lifecycle
onMounted(() => {
  loadPayments();
});
</script>

<style scoped>
.payment-management {
  padding: 1.5rem;
}

.header {
  margin-bottom: 2rem;
}

.header h2 {
  margin-bottom: 1.5rem;
  color: var(--text-color);
}

.filters {
  display: flex;
  gap: 1.5rem;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.875rem;
  color: var(--text-color);
  font-weight: 500;
}

.filter-group select,
.filter-group input {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  min-width: 150px;
}

.date-inputs {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.apply-btn {
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.apply-btn:hover {
  background: var(--primary-color-dark);
}

.payments-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

th {
  background: #f9fafb;
  font-weight: 500;
  color: var(--text-color);
}

.booking-info,
.customer-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.booking-id,
.email {
  font-size: 0.875rem;
  color: #6B7280;
}

.payment-method {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.payment-method.gcash {
  background: #E0F2FE;
  color: #0369A1;
}

.payment-method.cash {
  background: #F3F4F6;
  color: #374151;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background: #FEF3C7;
  color: #92400E;
}

.status-badge.completed {
  background: #DEF7EC;
  color: #03543F;
}

.status-badge.failed {
  background: #FEE2E2;
  color: #991B1B;
}

.status-badge.refunded {
  background: #E0F2FE;
  color: #0369A1;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.375rem 0.75rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.approve {
  background: #DEF7EC;
  color: #03543F;
}

.action-btn.reject {
  background: #FEE2E2;
  color: #991B1B;
}

.action-btn.view {
  background: #F3F4F6;
  color: #374151;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  font-size: 1.25rem;
}

.modal-body {
  padding: 1.5rem;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.detail-group h4 {
  margin: 0 0 1rem;
  color: var(--text-color);
}

.detail-group p {
  margin: 0.5rem 0;
  color: #374151;
}

.receipt-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.receipt-section h4 {
  margin: 0 0 1rem;
  color: var(--text-color);
}

.receipt-image {
  margin-bottom: 1rem;
}

.receipt-image img {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s;
}

.receipt-image img:hover {
  transform: scale(1.02);
}

.download-receipt {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #F3F4F6;
  color: #374151;
  text-decoration: none;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.download-receipt:hover {
  background: #E5E7EB;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.image-preview-modal .modal-content {
  background: none;
  width: auto;
  max-width: 90%;
  max-height: 90vh;
}

.image-preview-modal img {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-preview {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group {
    width: 100%;
  }

  .date-inputs {
    flex-direction: column;
  }

  .actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }

  .modal-content {
    width: 95%;
    margin: 1rem;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style> 