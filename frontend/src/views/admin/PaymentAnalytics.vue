<template>
  <div class="payment-analytics">
    <div class="analytics-header">
      <h1>Payment Analytics</h1>
      <div class="date-filter">
        <button 
          v-for="period in periods" 
          :key="period.value"
          :class="['filter-btn', { active: selectedPeriod === period.value }]"
          @click="selectedPeriod = period.value"
        >
          {{ period.label }}
        </button>
      </div>
    </div>

    <div class="analytics-grid">
      <!-- Overview Cards -->
      <div class="overview-cards">
        <div class="card total-revenue">
          <div class="card-icon">
            <i class="fas fa-money-bill-wave"></i>
          </div>
          <div class="card-content">
            <h3>Total Revenue</h3>
            <p class="amount">₱{{ formatPrice(totalRevenue) }}</p>
            <p class="trend" :class="{ up: revenueGrowth > 0 }">
              <i :class="revenueGrowth > 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(revenueGrowth) }}% from last period
            </p>
          </div>
        </div>

        <div class="card successful-payments">
          <div class="card-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-content">
            <h3>Successful Payments</h3>
            <p class="amount">{{ successfulPayments }}</p>
            <p class="success-rate">{{ successRate }}% success rate</p>
          </div>
        </div>

        <div class="card pending-payments">
          <div class="card-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="card-content">
            <h3>Pending Payments</h3>
            <p class="amount">{{ pendingPayments }}</p>
            <p class="pending-amount">₱{{ formatPrice(pendingAmount) }}</p>
          </div>
        </div>

        <div class="card average-booking">
          <div class="card-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="card-content">
            <h3>Average Booking Value</h3>
            <p class="amount">₱{{ formatPrice(averageBookingValue) }}</p>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-container">
        <div class="chart revenue-chart">
          <h3>Revenue Trend</h3>
          <canvas ref="revenueChartRef"></canvas>
        </div>

        <div class="chart payment-methods-chart">
          <h3>Payment Methods Distribution</h3>
          <canvas ref="methodsChartRef"></canvas>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="recent-transactions">
        <div class="table-header">
          <h3>Recent Transactions</h3>
          <button class="export-btn" @click="exportTransactions">
            <i class="fas fa-download"></i>
            Export
          </button>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in recentTransactions" :key="transaction.id">
                <td>{{ transaction.id }}</td>
                <td>{{ transaction.customerName }}</td>
                <td>₱{{ formatPrice(transaction.amount) }}</td>
                <td>
                  <span class="payment-method" :class="transaction.method">
                    {{ transaction.method }}
                  </span>
                </td>
                <td>
                  <span class="status" :class="transaction.status">
                    {{ transaction.status }}
                  </span>
                </td>
                <td>{{ formatDate(transaction.date) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart } from 'chart.js/auto';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';

const { get } = useApi();
const { showNotification } = useNotifications();

// Refs for chart canvases
const revenueChartRef = ref(null);
const methodsChartRef = ref(null);

// Data refs
const selectedPeriod = ref('month');
const totalRevenue = ref(0);
const revenueGrowth = ref(0);
const successfulPayments = ref(0);
const pendingPayments = ref(0);
const pendingAmount = ref(0);
const averageBookingValue = ref(0);
const successRate = ref(0);
const recentTransactions = ref([]);

// Filter periods
const periods = [
  { label: '7 Days', value: 'week' },
  { label: '30 Days', value: 'month' },
  { label: '90 Days', value: 'quarter' },
  { label: 'Year', value: 'year' }
];

// Charts
let revenueChart = null;
let methodsChart = null;

// Methods
const formatPrice = (price) => {
  return price.toLocaleString('en-PH');
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const fetchAnalytics = async () => {
  try {
    const response = await get(`/analytics/payments?period=${selectedPeriod.value}`);
    const data = response.data;

    // Update stats
    totalRevenue.value = data.totalRevenue;
    revenueGrowth.value = data.revenueGrowth;
    successfulPayments.value = data.successfulPayments;
    pendingPayments.value = data.pendingPayments;
    pendingAmount.value = data.pendingAmount;
    averageBookingValue.value = data.averageBookingValue;
    successRate.value = data.successRate;
    recentTransactions.value = data.recentTransactions;

    // Update charts
    updateRevenueChart(data.revenueTrend);
    updateMethodsChart(data.paymentMethods);
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to fetch analytics data'
    });
  }
};

const updateRevenueChart = (data) => {
  if (revenueChart) {
    revenueChart.destroy();
  }

  const ctx = revenueChartRef.value.getContext('2d');
  revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Revenue',
        data: data.values,
        borderColor: '#4CAF50',
        backgroundColor: 'rgba(76, 175, 80, 0.1)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => '₱' + value.toLocaleString()
          }
        }
      }
    }
  });
};

const updateMethodsChart = (data) => {
  if (methodsChart) {
    methodsChart.destroy();
  }

  const ctx = methodsChartRef.value.getContext('2d');
  methodsChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.labels,
      datasets: [{
        data: data.values,
        backgroundColor: [
          '#4CAF50',
          '#2196F3',
          '#FFC107',
          '#9C27B0'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }
  });
};

const exportTransactions = async () => {
  try {
    const response = await get('/analytics/payments/export', {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transactions-${selectedPeriod.value}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to export transactions'
    });
  }
};

// Watch for period changes
watch(selectedPeriod, () => {
  fetchAnalytics();
});

// Initialize
onMounted(() => {
  fetchAnalytics();
});
</script>

<style scoped>
.payment-analytics {
  padding: 2rem;
}

.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.date-filter {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--background-color);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.analytics-grid {
  display: grid;
  gap: 2rem;
}

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.card {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  font-size: 1.5rem;
}

.total-revenue .card-icon {
  background: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.successful-payments .card-icon {
  background: rgba(33, 150, 243, 0.1);
  color: #2196F3;
}

.pending-payments .card-icon {
  background: rgba(255, 193, 7, 0.1);
  color: #FFC107;
}

.average-booking .card-icon {
  background: rgba(156, 39, 176, 0.1);
  color: #9C27B0;
}

.amount {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0.5rem 0;
}

.trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.9rem;
  color: #f44336;
}

.trend.up {
  color: #4CAF50;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

.chart {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  height: 400px;
}

.recent-transactions {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.export-btn:hover {
  background: var(--primary-color-dark);
}

.table-container {
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
  font-weight: 600;
  color: var(--text-muted);
}

.payment-method {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
}

.payment-method.gcash {
  background: rgba(33, 150, 243, 0.1);
  color: #2196F3;
}

.payment-method.cash {
  background: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
}

.status.completed {
  background: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.status.pending {
  background: rgba(255, 193, 7, 0.1);
  color: #FFC107;
}

.status.failed {
  background: rgba(244, 67, 54, 0.1);
  color: #F44336;
}

@media (max-width: 768px) {
  .payment-analytics {
    padding: 1rem;
  }

  .analytics-header {
    flex-direction: column;
    gap: 1rem;
  }

  .date-filter {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 0.5rem;
  }

  .charts-container {
    grid-template-columns: 1fr;
  }
}
</style> 