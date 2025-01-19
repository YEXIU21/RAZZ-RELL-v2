<template>
  <div class="bookings-management">
    <main class="management-content">
      <div class="page-header">
        <h1>Archived Bookings</h1>
        <div class="header-actions">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search archived bookings..."
              @input="handleSearch"
              class="search-input"
            />
          </div>
          <div class="filters">
            <select v-model="statusFilter" class="filter-select">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <button class="filter-btn" @click="applyFilters">
              <i class="fas fa-filter"></i>
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="loading">
        Loading archived bookings...
      </div>
      <div v-else class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Event Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="booking in archivedBookings" :key="booking.id">
              <td>#{{ booking.id }}</td>
              <td>{{ booking.full_name }}</td>
              <td>{{ formatDate(booking.event_date) }}</td>
              <td>
                <span class="status-badge" :class="booking.status">
                  {{ booking.status }}
                </span>
              </td>
              <td>
                <div class="actions">
                  <button 
                    class="btn-action restore"
                    @click="confirmRestore(booking)"
                    title="Restore booking"
                  >
                    <i class="fas fa-undo"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const archivedBookings = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const fetchArchivedBookings = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/bookings/archived`);
    archivedBookings.value = response.data;
  } catch (error) {
    console.error('Error fetching archived bookings:', error);
    Swal.fire('Error', 'Failed to fetch archived bookings', 'error');
  } finally {
    loading.value = false;
  }
};

const confirmRestore = async (booking) => {
  const result = await Swal.fire({
    title: 'Restore Booking?',
    text: "This booking will be moved back to active bookings",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, restore it!'
  });

  if (result.isConfirmed) {
    await restoreBooking(booking.id);
  }
};

const restoreBooking = async (id) => {
  try {
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/bookings/${id}/restore`,
      {},
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      }
    );

    if (response.data.status === 200) {
      Swal.fire('Success', 'Booking restored successfully', 'success');
      await fetchArchivedBookings();
    }
  } catch (error) {
    Swal.fire('Error', 'Failed to restore booking', 'error');
  }
};

const handleSearch = () => {
  // Implement search functionality
};

const applyFilters = () => {
  // Implement filter functionality
};

onMounted(() => {
  fetchArchivedBookings();
});
</script>

<style scoped>
.bookings-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 1.5rem 2rem;
  margin-left: 250px;
  max-width: 1600px;
  margin: 0 auto;
  background: var(--background-color);
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.5rem;
}

.page-header h1 {
  color: var(--text-color);
}

.header-actions {
  display: flex;
  align-items: center;
  margin-top: 1rem;
  gap: 1rem;
}

.search-bar {
  position: relative;
  flex: 1;
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
}

.search-input {
  width: 100%;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
}

.search-input::placeholder {
  color: var(--text-secondary);
}

.filters {
  display: flex;
  gap: 0.5rem;
}

.filter-select {
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
}

.filter-btn {
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.filter-btn:hover {
  background: var(--primary-hover);
}

.table-container {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1rem;
  box-shadow: var(--shadow-sm);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-color);
}

th {
  font-weight: 600;
  color: var(--text-color);
  background: var(--hover-background);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status-badge.confirmed {
  background: var(--primary-light);
  color: var(--primary-color);
}

.status-badge.completed {
  background: var(--success-light);
  color: var(--success-color);
}

.status-badge.cancelled {
  background: var(--error-light);
  color: var(--error-color);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-action {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background: var(--hover-background);
  color: var(--text-color);
  transition: all 0.2s ease;
}

.btn-action:hover {
  background: var(--hover-color);
}

.btn-action.restore {
  color: var(--primary-color);
}

.btn-action.restore:hover {
  background: var(--primary-light);
}

.loading {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .management-content {
    padding: 1rem;
  }
  
  .header-actions {
    flex-direction: column;
  }
  
  .filters {
    width: 100%;
  }
  
  .filter-select {
    flex: 1;
  }
}
</style>