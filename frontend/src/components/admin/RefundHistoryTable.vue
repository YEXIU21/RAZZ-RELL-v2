`<template>
  <div class="refund-history">
    <div class="table-header">
      <div class="filters">
        <div class="search">
          <i class="fas fa-search"></i>
          <input 
            type="text" 
            v-model="search" 
            placeholder="Search by ID or customer name"
          >
        </div>
        <select v-model="statusFilter">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
        </select>
        <select v-model="dateRange">
          <option value="7">Last 7 Days</option>
          <option value="30">Last 30 Days</option>
          <option value="90">Last 90 Days</option>
          <option value="365">Last Year</option>
        </select>
      </div>
      <button class="export-btn" @click="exportRefunds">
        <i class="fas fa-download"></i>
        Export
      </button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>
              <button @click="sort('createdAt')">
                Date
                <i class="fas" :class="getSortIcon('createdAt')"></i>
              </button>
            </th>
            <th>
              <button @click="sort('transactionId')">
                Transaction ID
                <i class="fas" :class="getSortIcon('transactionId')"></i>
              </button>
            </th>
            <th>
              <button @click="sort('customerName')">
                Customer
                <i class="fas" :class="getSortIcon('customerName')"></i>
              </button>
            </th>
            <th>
              <button @click="sort('amount')">
                Amount
                <i class="fas" :class="getSortIcon('amount')"></i>
              </button>
            </th>
            <th>
              <button @click="sort('reason')">
                Reason
                <i class="fas" :class="getSortIcon('reason')"></i>
              </button>
            </th>
            <th>
              <button @click="sort('status')">
                Status
                <i class="fas" :class="getSortIcon('status')"></i>
              </button>
            </th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="refund in sortedRefunds" :key="refund._id">
            <td>{{ formatDate(refund.createdAt) }}</td>
            <td>{{ refund.transactionId }}</td>
            <td>
              <div class="customer-info">
                <span class="name">{{ refund.customerName }}</span>
                <span class="email">{{ refund.customerEmail }}</span>
              </div>
            </td>
            <td>₱{{ formatNumber(refund.amount) }}</td>
            <td>
              <div class="reason-cell">
                <span class="reason">{{ formatReason(refund.reason) }}</span>
                <button 
                  v-if="refund.notes"
                  class="notes-btn"
                  @click="showNotes(refund)"
                >
                  <i class="fas fa-sticky-note"></i>
                </button>
              </div>
            </td>
            <td>
              <span :class="['status-badge', refund.status]">
                {{ refund.status }}
              </span>
            </td>
            <td>
              <div class="actions">
                <button 
                  v-if="refund.status === 'pending'"
                  class="approve-btn"
                  @click="approveRefund(refund)"
                >
                  <i class="fas fa-check"></i>
                  Approve
                </button>
                <button 
                  v-if="refund.status === 'pending'"
                  class="reject-btn"
                  @click="rejectRefund(refund)"
                >
                  <i class="fas fa-times"></i>
                  Reject
                </button>
                <button 
                  class="details-btn"
                  @click="viewDetails(refund)"
                >
                  <i class="fas fa-eye"></i>
                  Details
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <button 
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';
import moment from 'moment';

const api = useApi();
const { showSuccess, showError } = useNotifications();

// State
const refunds = ref([]);
const search = ref('');
const statusFilter = ref('');
const dateRange = ref('30');
const currentPage = ref(1);
const itemsPerPage = 10;
const sortField = ref('createdAt');
const sortDirection = ref('desc');
const isLoading = ref(false);

// Computed
const filteredRefunds = computed(() => {
  return refunds.value.filter(refund => {
    const matchesSearch = search.value ? 
      (refund.transactionId.toLowerCase().includes(search.value.toLowerCase()) ||
       refund.customerName.toLowerCase().includes(search.value.toLowerCase())) :
      true;

    const matchesStatus = statusFilter.value ? 
      refund.status === statusFilter.value :
      true;

    const matchesDate = isWithinDateRange(refund.createdAt);

    return matchesSearch && matchesStatus && matchesDate;
  });
});

const sortedRefunds = computed(() => {
  const sorted = [...filteredRefunds.value].sort((a, b) => {
    if (sortField.value === 'amount') {
      return sortDirection.value === 'asc' ? 
        a.amount - b.amount :
        b.amount - a.amount;
    }
    
    if (sortField.value === 'createdAt') {
      return sortDirection.value === 'asc' ?
        new Date(a.createdAt) - new Date(b.createdAt) :
        new Date(b.createdAt) - new Date(a.createdAt);
    }

    const aValue = a[sortField.value].toLowerCase();
    const bValue = b[sortField.value].toLowerCase();
    return sortDirection.value === 'asc' ?
      aValue.localeCompare(bValue) :
      bValue.localeCompare(aValue);
  });

  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return sorted.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredRefunds.value.length / itemsPerPage);
});

// Methods
const fetchRefunds = async () => {
  try {
    isLoading.value = true;
    const response = await api.get('/payments/refunds');
    refunds.value = response.data;
  } catch (error) {
    showError('Failed to load refund history');
    console.error('Fetch refunds error:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (date) => {
  return moment(date).format('MMM D, YYYY h:mm A');
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-PH').format(num.toFixed(2));
};

const formatReason = (reason) => {
  return reason.split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const isWithinDateRange = (date) => {
  const days = parseInt(dateRange.value);
  const start = moment().subtract(days, 'days');
  return moment(date).isAfter(start);
};

const sort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};

const getSortIcon = (field) => {
  if (sortField.value !== field) return 'fa-sort';
  return sortDirection.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
};

const approveRefund = async (refund) => {
  try {
    await api.post(`/payments/refunds/${refund._id}/approve`);
    showSuccess('Refund approved successfully');
    fetchRefunds();
  } catch (error) {
    showError('Failed to approve refund');
    console.error('Approve refund error:', error);
  }
};

const rejectRefund = async (refund) => {
  try {
    await api.post(`/payments/refunds/${refund._id}/reject`);
    showSuccess('Refund rejected successfully');
    fetchRefunds();
  } catch (error) {
    showError('Failed to reject refund');
    console.error('Reject refund error:', error);
  }
};

const exportRefunds = async () => {
  try {
    const response = await api.get('/payments/refunds/export', {
      params: {
        search: search.value,
        status: statusFilter.value,
        days: dateRange.value
      },
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `refunds_${moment().format('YYYY-MM-DD')}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    showError('Failed to export refunds');
    console.error('Export refunds error:', error);
  }
};

// Watch for filter changes
watch([search, statusFilter, dateRange], () => {
  currentPage.value = 1;
});

// Initialize
fetchRefunds();
</script>

<style scoped>
.refund-history {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.table-header {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
}

.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search {
  position: relative;
}

.search i {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search input {
  padding: 0.75rem;
  padding-left: 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
  width: 300px;
}

select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

.export-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: var(--background-secondary);
  padding: 1rem;
  text-align: left;
  color: var(--text-muted);
  font-weight: 500;
}

th button {
  background: none;
  border: none;
  color: inherit;
  font-weight: inherit;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.customer-info {
  display: flex;
  flex-direction: column;
}

.customer-info .email {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.reason-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.notes-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.25rem;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
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

.actions {
  display: flex;
  gap: 0.5rem;
}

.approve-btn,
.reject-btn,
.details-btn {
  padding: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.approve-btn {
  background: #DEF7EC;
  color: #03543F;
}

.reject-btn {
  background: #FEE2E2;
  color: #991B1B;
}

.details-btn {
  background: var(--background-secondary);
  color: var(--text-color);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.pagination button {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background: var(--input-background);
  color: var(--text-color);
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 1024px) {
  .filters {
    flex-wrap: wrap;
  }

  .search input {
    width: 200px;
  }
}

@media (max-width: 768px) {
  .table-header {
    flex-direction: column;
    gap: 1rem;
  }

  .filters {
    width: 100%;
  }

  .export-btn {
    width: 100%;
    justify-content: center;
  }

  .actions {
    flex-direction: column;
  }

  .approve-btn,
  .reject-btn,
  .details-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>` 