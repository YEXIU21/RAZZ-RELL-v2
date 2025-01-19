<template>
  <div class="booking-details">
    <div class="details-container">
      <!-- Booking Information -->
      <div class="booking-info">
        <h2>Booking Details</h2>
        <div class="info-grid">
          <div class="info-group">
            <h3>Event Information</h3>
            <div class="info-item">
              <span class="label">Event Date:</span>
              <span class="value">{{ formatDate(booking?.event_date) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Event Time:</span>
              <span class="value">{{ formatTime(booking?.event_time) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Venue:</span>
              <span class="value">{{ booking?.venue_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Status:</span>
              <span class="value status-badge" :class="booking?.status">
                {{ booking?.status }}
              </span>
            </div>
          </div>

          <div class="info-group">
            <h3>Package Details</h3>
            <div class="info-item">
              <span class="label">Package:</span>
              <span class="value">{{ booking?.package?.package_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Number of Guests:</span>
              <div class="packs-input-container">
                <input 
                  type="number"
                  v-model.number="guestCount"
                  @input="validateAndUpdateGuests"
                  min="50"
                  max="500"
                  step="50"
                  class="packs-input"
                />
                <span class="pax-label">pax</span>
              </div>
            </div>
            <div class="info-item">
              <span class="label">Total Amount:</span>
              <span class="value amount">₱{{ formatPrice(booking?.total_price) }}</span>
            </div>
            <!-- Add Payment Details Breakdown -->
            <div class="payment-breakdown">
              <h4>Payment Breakdown</h4>
              <div class="breakdown-item">
                <span>Base Package Price:</span>
                <span>₱{{ formatPrice(booking?.package?.package_price) }}</span>
              </div>
              <div class="breakdown-item" v-if="booking?.packs > 50">
                <span>Additional Pax Charge:</span>
                <span>+ ₱{{ formatPrice(calculateAdditionalPaxCharge()) }}</span>
              </div>
              <div class="breakdown-item total">
                <span>Total:</span>
                <span>₱{{ formatPrice(booking?.total_price) }}</span>
              </div>
            </div>
          </div>

          <div class="info-group">
            <h3>Contact Information</h3>
            <div class="info-item">
              <span class="label">Full Name:</span>
              <span class="value">{{ booking?.full_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Email:</span>
              <span class="value">{{ booking?.email }}</span>
            </div>
            <div class="info-item">
              <span class="label">Phone:</span>
              <span class="value">{{ booking?.phone }}</span>
            </div>
          </div>

          <div class="info-group" v-if="booking?.special_requests">
            <h3>Special Requests</h3>
            <div class="info-item">
              <p class="special-requests">{{ booking?.special_requests }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Chat Section -->
      <div class="chat-section">
        <BookingChat 
          :booking-id="bookingId" 
          :current-user-id="currentUserId"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import BookingChat from '@/components/chat/BookingChat.vue';

const route = useRoute();
const bookingId = route.params.id;
const booking = ref(null);
const currentUserId = ref(null);
const guestCount = ref(booking.value?.packs || 50);

const loadBooking = async () => {
  try {
    const response = await axios.get(`/api/bookings/${bookingId}`);
    booking.value = response.data;
  } catch (error) {
    console.error('Error loading booking:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load booking details'
    });
  }
};

const getCurrentUser = async () => {
  try {
    const userInfo = JSON.parse(localStorage.getItem('user_info'));
    currentUserId.value = userInfo.id;
  } catch (error) {
    console.error('Error getting current user:', error);
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatTime = (time) => {
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatPrice = (price) => {
  return Number(price).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const validateAndUpdateGuests = (event) => {
  let value = parseInt(event.target.value) || 50;
  // Round to nearest 50
  value = Math.round(value / 50) * 50;
  // Ensure between 50 and 500
  value = Math.max(50, Math.min(500, value));
  guestCount.value = value;
  
  // Update the booking if needed
  updateBookingPacks(value);
};

const updateBookingPacks = async (value) => {
  try {
    await axios.put(`/api/bookings/${bookingId}`, {
      id: bookingId,
      packs: value
    });
    // Optionally refresh booking data
    await loadBooking();
  } catch (error) {
    console.error('Error updating packs:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update number of guests'
    });
  }
};

const calculateAdditionalPaxCharge = () => {
  if (!booking.value?.package?.package_price || !booking.value?.packs) return 0;
  
  const basePrice = booking.value.package.package_price;
  const additionalPax = Math.max(0, booking.value.packs - 50);
  const pricePerPax = basePrice / 50; // Calculate price per person based on base package
  
  return additionalPax * pricePerPax;
};

onMounted(() => {
  loadBooking();
  getCurrentUser();
});
</script>

<style scoped>
.booking-details {
  padding: 2rem;
  background: var(--background-color);
  min-height: 100vh;
}

.details-container {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.main-content {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-md);
  padding: 1.5rem;
  border: 1px solid var(--border-color);
}

h2 {
  margin: 0 0 1.5rem;
  color: var(--text-color);
}

.info-grid {
  display: grid;
  gap: 2rem;
}

.info-group h3 {
  margin: 0 0 1rem;
  color: var(--text-color);
  font-size: 1.1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.label {
  color: var(--text-muted);
  font-weight: 500;
}

.value {
  color: var(--text-color);
}

.value.amount {
  font-weight: 600;
  color: var(--primary-color);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status-badge.confirmed {
  background: var(--success-light);
  color: var(--success-color);
}

.status-badge.completed {
  background: var(--info-light);
  color: var(--info-color);
}

.status-badge.cancelled {
  background: var(--error-light);
  color: var(--error-color);
}

.special-requests {
  margin: 0;
  color: var(--text-color);
  line-height: 1.5;
}

.chat-section {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-md);
  height: 100%;
  display: flex;
  flex-direction: column;
  border: 1px solid var(--border-color);
}

.chat-header {
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.chat-header h3 {
  margin: 0;
  color: var(--text-color);
  font-size: 1.1rem;
}

.chat-messages {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.message.outgoing {
  flex-direction: row-reverse;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-color);
  font-weight: 600;
}

.message-content {
  background: var(--hover-background);
  padding: 1rem;
  border-radius: 12px;
  max-width: 70%;
}

.message.outgoing .message-content {
  background: var(--primary-light);
  color: var(--text-color);
}

.sender-name {
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 0.25rem;
}

.message-text {
  color: var(--text-color);
  line-height: 1.5;
}

.message-time {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

.chat-input {
  padding: 1rem;
  border-top: 1px solid var(--border-color);
}

.input-group {
  display: flex;
  gap: 0.5rem;
}

.message-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  resize: none;
}

.message-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-light);
}

.send-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.send-btn:hover {
  background: var(--primary-hover);
}

.payment-breakdown {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 2rem;
  border: 1px solid var(--border-color);
}

.payment-breakdown h3 {
  color: var(--text-color);
  margin: 0 0 1rem;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  color: var(--text-muted);
}

.breakdown-item.total {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px dashed var(--border-color);
  font-weight: 600;
  color: var(--text-color);
}

@media (max-width: 1024px) {
  .details-container {
    grid-template-columns: 1fr;
  }

  .chat-section {
    height: 600px;
  }
}

@media (max-width: 768px) {
  .booking-details {
    padding: 1rem;
  }

  .info-item {
    flex-direction: column;
    gap: 0.25rem;
  }

  .value {
    padding-left: 1rem;
  }

  .info-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .packs-input-container {
    margin-top: 0.5rem;
  }

  .payment-breakdown {
    margin-top: 1.5rem;
  }
  
  .breakdown-item {
    flex-direction: row;
    gap: 0.5rem;
  }
}
</style> 