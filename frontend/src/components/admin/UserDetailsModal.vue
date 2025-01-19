<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>User Details</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="user-details">
        <div class="user-profile">
          <div class="profile-image">
            <img :src="user.avatar || '/default-avatar.png'" :alt="user.firstName" />
          </div>
          <div class="profile-info">
            <h3>{{ user.firstName }} {{ user.lastName }}</h3>
            <div class="badges">
              <span class="role-badge" :class="user.role">
                {{ user.role }}
              </span>
              <span class="status-badge" :class="user.status">
                {{ user.status }}
              </span>
            </div>
          </div>
        </div>

        <div class="details-section">
          <h3>Contact Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Email</span>
              <span class="value">
                <i class="fas fa-envelope"></i>
                {{ user.email }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Phone</span>
              <span class="value">
                <i class="fas fa-phone"></i>
                {{ user.phone }}
              </span>
            </div>
            <div class="info-item full-width">
              <span class="label">Address</span>
              <span class="value">
                <i class="fas fa-map-marker-alt"></i>
                {{ formatAddress }}
              </span>
            </div>
          </div>
        </div>

        <div class="details-section">
          <h3>Account Activity</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Member Since</span>
              <span class="value">
                <i class="fas fa-calendar"></i>
                {{ formatDate(user.createdAt) }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Last Login</span>
              <span class="value">
                <i class="fas fa-clock"></i>
                {{ formatDateTime(user.lastLogin) }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Total Bookings</span>
              <span class="value">
                <i class="fas fa-calendar-check"></i>
                {{ user.bookingsCount }} bookings
              </span>
            </div>
            <div class="info-item">
              <span class="label">Total Spent</span>
              <span class="value">
                <i class="fas fa-coins"></i>
                ₱{{ formatNumber(user.totalSpent) }}
              </span>
            </div>
          </div>
        </div>

        <div class="details-section">
          <div class="section-header">
            <h3>Recent Bookings</h3>
            <button class="view-all-btn" @click="viewAllBookings">
              View All
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
          
          <div class="bookings-list">
            <div v-for="booking in user.recentBookings" :key="booking.id" class="booking-item">
              <div class="booking-info">
                <span class="booking-id">#{{ booking.id }}</span>
                <span class="event-type" :class="booking.eventType.toLowerCase()">
                  {{ booking.eventType }}
                </span>
                <span class="booking-date">
                  {{ formatDate(booking.eventDate) }}
                </span>
              </div>
              <div class="booking-status">
                <span class="status" :class="booking.status.toLowerCase()">
                  {{ booking.status }}
                </span>
                <span class="amount">₱{{ formatNumber(booking.amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="details-section">
          <h3>Security Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Two-Factor Auth</span>
              <span class="value">
                <i class="fas" :class="user.twoFactorEnabled ? 'fa-check-circle' : 'fa-times-circle'"></i>
                {{ user.twoFactorEnabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Last Password Change</span>
              <span class="value">
                <i class="fas fa-key"></i>
                {{ formatDate(user.lastPasswordChange) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="action-btn edit" @click="editUser">
          <i class="fas fa-edit"></i>
          Edit User
        </button>
        <button 
          class="action-btn" 
          :class="user.status === 'blocked' ? 'unblock' : 'block'"
          @click="toggleBlock"
        >
          <i class="fas" :class="user.status === 'blocked' ? 'fa-unlock' : 'fa-ban'"></i>
          {{ user.status === 'blocked' ? 'Unblock User' : 'Block User' }}
        </button>
        <button class="action-btn delete" @click="deleteUser">
          <i class="fas fa-trash"></i>
          Delete User
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'edit', 'block', 'delete']);
const router = useRouter();

const formatAddress = computed(() => {
  const parts = [props.user.address, props.user.city, props.user.postalCode];
  return parts.filter(Boolean).join(', ');
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatNumber = (num) => {
  return num.toLocaleString();
};

const editUser = () => {
  emit('edit');
};

const toggleBlock = () => {
  emit('block');
};

const deleteUser = () => {
  emit('delete');
};

const viewAllBookings = () => {
  router.push({
    path: '/admin/bookings',
    query: { user: props.user.id }
  });
  emit('close');
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
  z-index: 1000;
}

.modal-content {
  background: var(--modal-background, #fff);
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-color);
  cursor: pointer;
  padding: 0.5rem;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.user-profile {
  display: flex;
  gap: 2rem;
  align-items: center;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.profile-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
}

.profile-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-info h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.badges {
  display: flex;
  gap: 1rem;
}

.role-badge, .status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.role-badge.admin {
  background: #e3f2fd;
  color: #1565c0;
}

.role-badge.staff {
  background: #e8f5e9;
  color: #2e7d32;
}

.role-badge.user {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #fff3cd;
  color: #856404;
}

.status-badge.blocked {
  background: #f8d7da;
  color: #721c24;
}

.details-section {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.details-section h3 {
  font-size: 1.2rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.view-all-btn {
  padding: 0.5rem 1rem;
  background: none;
  border: none;
  color: var(--primary-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item.full-width {
  grid-column: 1 / -1;
}

.label {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-color);
}

.value i {
  color: var(--primary-color);
  width: 16px;
}

.bookings-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--background-color);
  border-radius: 6px;
}

.booking-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.booking-id {
  font-weight: 500;
  color: var(--text-color);
}

.event-type {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.event-type.wedding {
  background: #e8f5e9;
  color: #2e7d32;
}

.event-type.debut {
  background: #fff3e0;
  color: #ef6c00;
}

.event-type.christening {
  background: #e3f2fd;
  color: #1565c0;
}

.event-type.party {
  background: #f3e5f5;
  color: #7b1fa2;
}

.booking-status {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.status.pending {
  background: #fff3cd;
  color: #856404;
}

.status.confirmed {
  background: #d4edda;
  color: #155724;
}

.status.completed {
  background: #cce5ff;
  color: #004085;
}

.status.cancelled {
  background: #f8d7da;
  color: #721c24;
}

.amount {
  font-weight: 500;
  color: var(--text-color);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);
}

.action-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: white;
  font-weight: 500;
}

.action-btn.edit {
  background: var(--warning-color, #ffc107);
}

.action-btn.block {
  background: var(--danger-color, #dc3545);
}

.action-btn.unblock {
  background: var(--success-color, #28a745);
}

.action-btn.delete {
  background: var(--danger-color, #dc3545);
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .user-profile {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .badges {
    justify-content: center;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .booking-item {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .booking-info, .booking-status {
    flex-direction: column;
    gap: 0.5rem;
  }

  .modal-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
    justify-content: center;
  }
}
</style> 