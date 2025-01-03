<template>
  <div class="refunds-list">
    <div class="header">
      <h2>Refund Requests</h2>
      <div class="filters">
        <select v-model="statusFilter" class="filter-select">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input 
            type="text"
            v-model="searchQuery"
            placeholder="Search by customer or booking ID"
          />
        </div>
      </div>
    </div>

    <div class="refunds-table">
      <div class="table-header">
        <div class="col">Customer</div>
        <div class="col">Booking ID</div>
        <div class="col">Amount</div>
        <div class="col">Reason</div>
        <div class="col">Status</div>
        <div class="col">Date</div>
        <div class="col">Actions</div>
      </div>

      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        Loading refunds...
      </div>

      <div v-else-if="filteredRefunds.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>No refund requests found</p>
      </div>

      <template v-else>
        <div 
          v-for="refund in filteredRefunds" 
          :key="refund.id"
          class="table-row"
          :class="{ 'expanded': expandedRefund === refund.id }"
        >
          <div class="row-main" @click="toggleExpand(refund.id)">
            <div class="col">{{ refund.customerName }}</div>
            <div class="col">{{ refund.bookingId }}</div>
            <div class="col amount">₱{{ formatPrice(refund.amount) }}</div>
            <div class="col reason">{{ truncateText(refund.reason, 30) }}</div>
            <div class="col">
              <span 
                class="status-badge"
                :class="refund.status"
              >
                {{ refund.status }}
              </span>
            </div>
            <div class="col">{{ formatDate(refund.createdAt) }}</div>
            <div class="col actions">
              <button 
                v-if="refund.status === 'pending'"
                class="action-btn approve"
                @click.stop="openRefundModal(refund)"
              >
                Process
              </button>
              <button 
                class="action-btn view"
                @click.stop="viewDetails(refund)"
              >
                View
              </button>
            </div>
          </div>

          <div v-if="expandedRefund === refund.id" class="row-details">
            <div class="details-grid">
              <div class="detail-group">
                <h4>Customer Details</h4>
                <p><strong>Name:</strong> {{ refund.customerName }}</p>
                <p><strong>Email:</strong> {{ refund.customerEmail }}</p>
                <p><strong>Phone:</strong> {{ refund.customerPhone }}</p>
              </div>

              <div class="detail-group">
                <h4>Booking Details</h4>
                <p><strong>Event Type:</strong> {{ refund.eventType }}</p>
                <p><strong>Event Date:</strong> {{ formatDate(refund.eventDate) }}</p>
                <p><strong>Package:</strong> {{ refund.packageName }}</p>
              </div>

              <div class="detail-group">
                <h4>Refund Details</h4>
                <p><strong>Amount:</strong> ₱{{ formatPrice(refund.amount) }}</p>
                <p><strong>Method:</strong> {{ refund.refundMethod }}</p>
                <p><strong>Status:</strong> {{ refund.status }}</p>
              </div>

              <div class="detail-group full-width">
                <h4>Reason</h4>
                <p>{{ refund.reason }}</p>
              </div>

              <div v-if="refund.cancellation_reason" class="detail-group full-width">
                <h4>Cancellation Reason</h4>
                <p class="cancellation-reason">{{ refund.cancellation_reason }}</p>
              </div>

              <div v-if="refund.adminNotes" class="detail-group full-width">
                <h4>Admin Notes</h4>
                <p>{{ refund.adminNotes }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <RefundManagementModal
      v-if="selectedRefund"
      :booking="selectedRefund"
      @close="closeRefundModal"
      @refund-processed="handleRefundProcessed"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';
import RefundManagementModal from './RefundManagementModal.vue';

const { get } = useApi();
const { showNotification } = useNotifications();

const refunds = ref([]);
const loading = ref(true);
const statusFilter = ref('');
const searchQuery = ref('');
const expandedRefund = ref(null);
const selectedRefund = ref(null);

const fetchRefunds = async () => {
  try {
    loading.value = true;
    const response = await get('/refunds');
    refunds.value = response.data.map(refund => ({
      ...refund,
      cancellation_reason: refund.booking?.cancellation_reason || null
    }));
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to fetch refund requests'
    });
  } finally {
    loading.value = false;
  }
};

const filteredRefunds = computed(() => {
  let filtered = refunds.value;

  if (statusFilter.value) {
    filtered = filtered.filter(refund => refund.status === statusFilter.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(refund => 
      refund.customerName.toLowerCase().includes(query) ||
      refund.bookingId.toLowerCase().includes(query)
    );
  }

  return filtered;
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

const truncateText = (text, length) => {
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};

const toggleExpand = (refundId) => {
  expandedRefund.value = expandedRefund.value === refundId ? null : refundId;
};

const openRefundModal = (refund) => {
  selectedRefund.value = refund;
};

const closeRefundModal = () => {
  selectedRefund.value = null;
};

const handleRefundProcessed = (updatedRefund) => {
  const index = refunds.value.findIndex(r => r.id === updatedRefund.id);
  if (index !== -1) {
    refunds.value[index] = updatedRefund;
  }
  closeRefundModal();
};

const viewDetails = (refund) => {
  toggleExpand(refund.id);
};

// Fetch refunds on component mount
fetchRefunds();
</script>

<style scoped>
.refunds-list {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.header h2 {
  margin-bottom: 1rem;
  color: var(--text-color);
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter-select {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
  min-width: 150px;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 300px;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-box input {
  width: 100%;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

.refunds-table {
  width: 100%;
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2fr 1fr 1.5fr 1fr;
  padding: 1rem;
  background: var(--background-color);
  border-bottom: 1px solid var(--border-color);
  font-weight: 500;
  color: var(--text-muted);
}

.table-row {
  border-bottom: 1px solid var(--border-color);
}

.row-main {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2fr 1fr 1.5fr 1fr;
  padding: 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.row-main:hover {
  background: var(--hover-color);
}

.col {
  display: flex;
  align-items: center;
  padding: 0 0.5rem;
}

.amount {
  font-weight: 500;
  color: var(--primary-color);
}

.reason {
  color: var(--text-muted);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.pending {
  background: var(--warning-color-light);
  color: var(--warning-color);
}

.status-badge.approved {
  background: var(--success-color-light);
  color: var(--success-color);
}

.status-badge.rejected {
  background: var(--danger-color-light);
  color: var(--danger-color);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.25rem 0.75rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn.approve {
  background: var(--primary-color);
  color: white;
}

.action-btn.view {
  background: var(--secondary-color);
  color: white;
}

.row-details {
  padding: 1.5rem;
  background: var(--background-color);
  border-bottom: 1px solid var(--border-color);
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.detail-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-group h4 {
  color: var(--text-color);
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.detail-group p {
  color: var(--text-muted);
  line-height: 1.5;
}

.detail-group.full-width {
  grid-column: 1 / -1;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: var(--text-muted);
  gap: 1rem;
}

.loading-state i,
.empty-state i {
  font-size: 2rem;
}

@media (max-width: 1024px) {
  .table-header,
  .row-main {
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
  }

  .col:nth-child(4),
  .col:nth-child(6) {
    display: none;
  }
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    max-width: none;
  }

  .table-header,
  .row-main {
    grid-template-columns: 2fr 1fr 1fr 1fr;
  }

  .col:nth-child(3) {
    display: none;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}

.cancellation-reason {
  padding: 1rem;
  background-color: var(--warning-color-light);
  border-left: 4px solid var(--warning-color);
  border-radius: 4px;
  margin-top: 0.5rem;
}
</style> 