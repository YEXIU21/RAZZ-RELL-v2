<template>
  <div class="transactions-management">
    <header class="page-header">
      <h1>Transactions Management</h1>
      <button class="export-btn" @click="showExportModal = true">
        <i class="fas fa-download"></i>
        Export Transactions
      </button>
      <div class="stats-cards">
        <div class="stat-card">
          <h3>Total Revenue</h3>
          <p>{{ formatCurrency(stats.totalRevenue) }}</p>
        </div>
        <div class="stat-card">
          <h3>Total Transactions</h3>
          <p>{{ stats.totalTransactions }}</p>
        </div>
        <div class="stat-card">
          <h3>Average Amount</h3>
          <p>{{ formatCurrency(stats.averageAmount) }}</p>
        </div>
      </div>
    </header>

    <!-- Filters -->
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
        <select v-model="filters.method">
          <option value="">All</option>
          <option value="stripe">Credit Card</option>
          <option value="paypal">PayPal</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Date Range</label>
        <div class="date-range">
          <input
            type="date"
            v-model="filters.startDate"
            :max="filters.endDate"
          />
          <span>to</span>
          <input
            type="date"
            v-model="filters.endDate"
            :min="filters.startDate"
          />
        </div>
      </div>

      <button class="apply-filters" @click="loadTransactions">
        Apply Filters
      </button>
    </div>

    <!-- Transactions Table -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="transaction in transactions" :key="transaction._id">
            <td>{{ transaction.transactionId }}</td>
            <td>{{ formatDate(transaction.createdAt) }}</td>
            <td>
              {{ transaction.user.firstName }} {{ transaction.user.lastName }}
            </td>
            <td>{{ formatCurrency(transaction.amount) }}</td>
            <td>
              <span :class="'method ' + transaction.method">
                {{ transaction.method === 'stripe' ? 'Credit Card' : 'PayPal' }}
              </span>
            </td>
            <td>
              <span :class="'status ' + transaction.status">
                {{ transaction.statusDescription }}
              </span>
            </td>
            <td>
              <button
                class="view-btn"
                @click="viewTransaction(transaction)"
              >
                View
              </button>
              <button
                v-if="transaction.canBeRefunded"
                class="refund-btn"
                @click="showRefundModal(transaction)"
              >
                Refund
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button
        :disabled="currentPage === 1"
        @click="changePage(currentPage - 1)"
      >
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button
        :disabled="currentPage === totalPages"
        @click="changePage(currentPage + 1)"
      >
        Next
      </button>
    </div>

    <!-- Transaction Details Modal -->
    <Modal
      v-if="selectedTransaction"
      @close="selectedTransaction = null"
      title="Transaction Details"
    >
      <div class="transaction-details">
        <div class="detail-group">
          <h3>Transaction Information</h3>
          <p><strong>ID:</strong> {{ selectedTransaction.transactionId }}</p>
          <p><strong>Date:</strong> {{ formatDate(selectedTransaction.createdAt) }}</p>
          <p><strong>Amount:</strong> {{ formatCurrency(selectedTransaction.amount) }}</p>
          <p><strong>Status:</strong> {{ selectedTransaction.statusDescription }}</p>
          <p><strong>Method:</strong> {{ selectedTransaction.method === 'stripe' ? 'Credit Card' : 'PayPal' }}</p>
          <div class="action-buttons">
            <button
              class="download-btn"
              @click="downloadReceipt(selectedTransaction.transactionId)"
            >
              <i class="fas fa-download"></i>
              Download Receipt
            </button>
          </div>
        </div>

        <div class="detail-group">
          <h3>Customer Information</h3>
          <p><strong>Name:</strong> {{ selectedTransaction.user.firstName }} {{ selectedTransaction.user.lastName }}</p>
          <p><strong>Email:</strong> {{ selectedTransaction.user.email }}</p>
        </div>

        <div class="detail-group">
          <h3>Booking Information</h3>
          <p><strong>Event Type:</strong> {{ selectedTransaction.booking.eventType }}</p>
          <p><strong>Event Date:</strong> {{ formatDate(selectedTransaction.booking.eventDate) }}</p>
          <p><strong>Status:</strong> {{ selectedTransaction.booking.status }}</p>
        </div>

        <div v-if="selectedTransaction.refund" class="detail-group">
          <h3>Refund Information</h3>
          <p><strong>Amount:</strong> {{ formatCurrency(selectedTransaction.refund.amount) }}</p>
          <p><strong>Date:</strong> {{ formatDate(selectedTransaction.refund.processedAt) }}</p>
          <p><strong>Reason:</strong> {{ selectedTransaction.refund.reason }}</p>
        </div>
      </div>
    </Modal>

    <!-- Refund Modal -->
    <Modal
      v-if="showingRefundModal"
      @close="showingRefundModal = false"
      title="Process Refund"
    >
      <div class="refund-form">
        <div class="form-group">
          <label>Refund Amount</label>
          <input
            type="number"
            v-model="refundForm.amount"
            :max="refundForm.maxAmount"
            step="0.01"
          />
          <small>Maximum amount: {{ formatCurrency(refundForm.maxAmount) }}</small>
        </div>

        <div class="form-group">
          <label>Reason for Refund</label>
          <textarea v-model="refundForm.reason" rows="3"></textarea>
        </div>

        <div class="form-actions">
          <button
            class="cancel-btn"
            @click="showingRefundModal = false"
          >
            Cancel
          </button>
          <button
            class="confirm-btn"
            :disabled="!isValidRefund"
            @click="processRefund"
          >
            Process Refund
          </button>
        </div>
      </div>
    </Modal>

    <!-- Export Modal -->
    <ExportTransactionsModal
      v-if="showExportModal"
      @close="showExportModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import Modal from '@/components/ui/Modal.vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';
import ExportTransactionsModal from '@/components/admin/ExportTransactionsModal.vue';

export default {
  name: 'TransactionsManagement',
  components: {
    Modal,
    ExportTransactionsModal
  },
  setup() {
    const api = useApi();
    const { showSuccess, showError } = useNotifications();

    // State
    const transactions = ref([]);
    const stats = ref({
      totalRevenue: 0,
      totalTransactions: 0,
      averageAmount: 0
    });
    const currentPage = ref(1);
    const totalPages = ref(1);
    const selectedTransaction = ref(null);
    const showingRefundModal = ref(false);
    const filters = ref({
      status: '',
      method: '',
      startDate: '',
      endDate: ''
    });
    const refundForm = ref({
      amount: 0,
      maxAmount: 0,
      reason: ''
    });
    const showExportModal = ref(false);

    // Computed
    const isValidRefund = computed(() => {
      return (
        refundForm.value.amount > 0 &&
        refundForm.value.amount <= refundForm.value.maxAmount &&
        refundForm.value.reason.trim().length > 0
      );
    });

    // Methods
    const loadTransactions = async () => {
      try {
        const { data } = await api.get('/api/payments/transactions', {
          params: {
            page: currentPage.value,
            ...filters.value
          }
        });
        transactions.value = data.transactions;
        totalPages.value = data.totalPages;
      } catch (error) {
        showError('Failed to load transactions');
      }
    };

    const loadStats = async () => {
      try {
        const { data } = await api.get('/api/payments/stats/overview');
        stats.value = data;
      } catch (error) {
        showError('Failed to load statistics');
      }
    };

    const changePage = (page) => {
      currentPage.value = page;
      loadTransactions();
    };

    const viewTransaction = (transaction) => {
      selectedTransaction.value = transaction;
    };

    const showRefundModal = (transaction) => {
      refundForm.value = {
        amount: transaction.amount,
        maxAmount: transaction.amount,
        reason: ''
      };
      selectedTransaction.value = transaction;
      showingRefundModal.value = true;
    };

    const processRefund = async () => {
      try {
        await api.post(`/api/payments/refund/${selectedTransaction.value.transactionId}`, {
          amount: refundForm.value.amount,
          reason: refundForm.value.reason
        });
        showSuccess('Refund processed successfully');
        showingRefundModal.value = false;
        loadTransactions();
      } catch (error) {
        showError('Failed to process refund');
      }
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount);
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const downloadReceipt = async (transactionId) => {
      try {
        const response = await api.get(`/api/payments/receipt/${transactionId}`, {
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `receipt-${transactionId}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        showError('Failed to download receipt');
      }
    };

    // Initialize
    onMounted(() => {
      loadTransactions();
      loadStats();
    });

    return {
      transactions,
      stats,
      currentPage,
      totalPages,
      selectedTransaction,
      showingRefundModal,
      filters,
      refundForm,
      isValidRefund,
      loadTransactions,
      changePage,
      viewTransaction,
      showRefundModal,
      processRefund,
      formatCurrency,
      formatDate,
      showExportModal,
      downloadReceipt
    };
  }
};
</script>

<style scoped>
.transactions-management {
  padding: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-card {
  background-color: var(--card-background);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: var(--card-shadow);
}

.stat-card h3 {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-card p {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--text-primary);
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.filter-group select,
.filter-group input {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--input-background);
}

.date-range {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.apply-filters {
  align-self: flex-end;
  padding: 0.5rem 1rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.table-container {
  background-color: var(--card-background);
  border-radius: 8px;
  box-shadow: var(--card-shadow);
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

th {
  background-color: var(--background-secondary);
  font-weight: 600;
}

.method,
.status {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.method.stripe {
  background-color: #6772e5;
  color: white;
}

.method.paypal {
  background-color: #003087;
  color: white;
}

.status.completed {
  background-color: var(--success-color-light);
  color: var(--success-color);
}

.status.pending {
  background-color: var(--warning-color-light);
  color: var(--warning-color);
}

.status.failed {
  background-color: var(--error-color-light);
  color: var(--error-color);
}

.status.refunded {
  background-color: var(--info-color-light);
  color: var(--info-color);
}

.view-btn,
.refund-btn {
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  margin-right: 0.5rem;
}

.view-btn {
  background-color: var(--primary-color-light);
  color: var(--primary-color);
}

.refund-btn {
  background-color: var(--warning-color-light);
  color: var(--warning-color);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--background-color);
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.transaction-details,
.refund-form {
  padding: 1.5rem;
}

.detail-group {
  margin-bottom: 1.5rem;
}

.detail-group h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: var(--text-secondary);
}

.detail-group p {
  margin: 0.25rem 0;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background-color: var(--input-background);
}

.form-group small {
  display: block;
  margin-top: 0.25rem;
  color: var(--text-secondary);
  font-size: 0.75rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.cancel-btn,
.confirm-btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.cancel-btn {
  background-color: var(--background-secondary);
  border: 1px solid var(--border-color);
}

.confirm-btn {
  background-color: var(--primary-color);
  color: white;
  border: none;
}

.confirm-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.export-btn i {
  font-size: 1rem;
}

.action-buttons {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
}

.download-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
}

.download-btn i {
  font-size: 1rem;
}

.stripe-btn {
  background-color: var(--primary-color);
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.stripe-btn:hover {
  background-color: var(--primary-hover);
}

.paypal-btn {
  background-color: var(--primary-color);
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.paypal-btn:hover {
  background-color: var(--primary-hover);
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .date-range {
    flex-direction: column;
  }

  .table-container {
    margin: 0 -1rem;
  }
}
</style> 