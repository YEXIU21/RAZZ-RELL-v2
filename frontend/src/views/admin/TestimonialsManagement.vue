<template>
  <div class="testimonials-management">
    <div class="page-header">
      <h1>Testimonials Management</h1>
      <div class="header-actions">
        <button 
          class="export-btn"
          @click="exportTestimonials"
          :disabled="isLoading"
        >
          <i class="fas fa-download"></i>
          Export Data
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
          <h3>Pending Review</h3>
          <p class="stat-value">{{ pendingCount }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon approved">
          <i class="fas fa-check"></i>
        </div>
        <div class="stat-info">
          <h3>Approved</h3>
          <p class="stat-value">{{ approvedCount }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon featured">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
          <h3>Featured</h3>
          <p class="stat-value">{{ featuredCount }}</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fas fa-comments"></i>
        </div>
        <div class="stat-info">
          <h3>Total</h3>
          <p class="stat-value">{{ totalTestimonials }}</p>
        </div>
      </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          type="text"
          v-model="searchQuery"
          placeholder="Search testimonials..."
        />
      </div>

      <div class="filter-controls">
        <select v-model="selectedStatus">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>

        <select v-model="selectedEventType">
          <option value="">All Event Types</option>
          <option value="wedding">Wedding</option>
          <option value="corporate">Corporate</option>
          <option value="birthday">Birthday</option>
          <option value="social">Social</option>
          <option value="other">Other</option>
        </select>

        <select v-model="sortBy">
          <option value="date-desc">Newest First</option>
          <option value="date-asc">Oldest First</option>
          <option value="rating-desc">Highest Rated</option>
          <option value="rating-asc">Lowest Rated</option>
        </select>
      </div>
    </div>

    <!-- Testimonials Table -->
    <div class="table-container">
      <table class="testimonials-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Event Type</th>
            <th>Rating</th>
            <th>Content</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="testimonial in filteredTestimonials" :key="testimonial.id">
            <td class="user-cell">
              <div class="user-info">
                <img 
                  :src="testimonial.userAvatar || '/default-avatar.png'"
                  :alt="testimonial.userName"
                />
                <span>{{ testimonial.userName }}</span>
              </div>
            </td>
            <td>{{ testimonial.eventType }}</td>
            <td class="rating-cell">
              <div class="rating">
                <i 
                  v-for="star in 5"
                  :key="star"
                  class="fas fa-star"
                  :class="{ active: star <= testimonial.rating }"
                ></i>
              </div>
            </td>
            <td class="content-cell">
              <p class="truncate">{{ testimonial.content }}</p>
              <button 
                class="view-more"
                @click="openTestimonial(testimonial)"
              >
                View More
              </button>
            </td>
            <td>
              <span 
                class="status-badge"
                :class="testimonial.status"
              >
                {{ testimonial.status }}
              </span>
            </td>
            <td>{{ formatDate(testimonial.date) }}</td>
            <td class="actions-cell">
              <template v-if="testimonial.status === 'pending'">
                <button 
                  class="approve-btn"
                  @click="approveTestimonial(testimonial.id)"
                  :disabled="isLoading"
                >
                  <i class="fas fa-check"></i>
                </button>
                <button 
                  class="reject-btn"
                  @click="openRejectModal(testimonial)"
                  :disabled="isLoading"
                >
                  <i class="fas fa-times"></i>
                </button>
              </template>
              <button 
                v-if="testimonial.status === 'approved'"
                class="feature-btn"
                :class="{ featured: testimonial.featured }"
                @click="toggleFeature(testimonial.id)"
                :disabled="isLoading"
              >
                <i class="fas fa-star"></i>
              </button>
              <button 
                class="delete-btn"
                @click="confirmDelete(testimonial)"
                :disabled="isLoading"
              >
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Load More -->
    <div class="load-more" v-if="hasMoreTestimonials">
      <button 
        class="load-more-btn"
        @click="loadMore"
        :disabled="isLoading"
      >
        <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
        <span v-else>Load More</span>
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="!filteredTestimonials.length" class="empty-state">
      <i class="fas fa-comments"></i>
      <h3>No testimonials found</h3>
      <p>Try adjusting your filters or search query</p>
    </div>

    <!-- View Modal -->
    <modal
      v-if="selectedTestimonial"
      @close="closeTestimonial"
      :title="'Testimonial from ' + selectedTestimonial.userName"
    >
      <div class="testimonial-details">
        <div class="user-header">
          <img 
            :src="selectedTestimonial.userAvatar || '/default-avatar.png'"
            :alt="selectedTestimonial.userName"
          />
          <div class="user-info">
            <h3>{{ selectedTestimonial.userName }}</h3>
            <p>{{ selectedTestimonial.eventType }}</p>
          </div>
          <div class="rating">
            <i 
              v-for="star in 5"
              :key="star"
              class="fas fa-star"
              :class="{ active: star <= selectedTestimonial.rating }"
            ></i>
          </div>
        </div>

        <div class="testimonial-content">
          {{ selectedTestimonial.content }}
        </div>

        <div v-if="selectedTestimonial.images?.length" class="testimonial-images">
          <img 
            v-for="(image, index) in selectedTestimonial.images"
            :key="index"
            :src="image"
            :alt="'Event photo ' + (index + 1)"
            @click="openImage(image)"
          />
        </div>

        <div class="meta-info">
          <span class="date">
            <i class="fas fa-calendar"></i>
            {{ formatDate(selectedTestimonial.date) }}
          </span>
          <span 
            class="status-badge"
            :class="selectedTestimonial.status"
          >
            {{ selectedTestimonial.status }}
          </span>
        </div>
      </div>
    </modal>

    <!-- Reject Modal -->
    <modal
      v-if="showRejectModal"
      @close="closeRejectModal"
      title="Reject Testimonial"
    >
      <div class="reject-form">
        <p>Please provide a reason for rejecting this testimonial:</p>
        <textarea
          v-model="rejectReason"
          placeholder="Enter rejection reason..."
          rows="4"
        ></textarea>
        <div class="modal-actions">
          <button 
            class="secondary-btn"
            @click="closeRejectModal"
          >
            Cancel
          </button>
          <button 
            class="primary-btn"
            @click="rejectTestimonial"
            :disabled="!rejectReason || isLoading"
          >
            <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
            <span v-else>Reject</span>
          </button>
        </div>
      </div>
    </modal>

    <!-- Delete Confirmation Modal -->
    <confirmation-modal
      v-if="showDeleteModal"
      @confirm="deleteTestimonial"
      @cancel="closeDeleteModal"
      title="Delete Testimonial"
      :message="'Are you sure you want to delete this testimonial from ' + testimonialToDelete?.userName + '? This action cannot be undone.'"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useTestimonials } from '@/composables/useTestimonials';
import Modal from '@/components/ui/Modal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';

const {
  testimonials,
  totalTestimonials,
  isLoading,
  fetchTestimonials,
  approveTestimonial: approve,
  rejectTestimonial: reject,
  deleteTestimonial: remove,
  hasMoreTestimonials,
  loadMore: loadMoreTestimonials
} = useTestimonials();

// UI State
const searchQuery = ref('');
const selectedStatus = ref('');
const selectedEventType = ref('');
const sortBy = ref('date-desc');
const selectedTestimonial = ref(null);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);
const rejectReason = ref('');
const testimonialToDelete = ref(null);

// Computed Properties
const pendingCount = computed(() =>
  testimonials.value.filter(t => t.status === 'pending').length
);

const approvedCount = computed(() =>
  testimonials.value.filter(t => t.status === 'approved').length
);

const featuredCount = computed(() =>
  testimonials.value.filter(t => t.featured).length
);

const filteredTestimonials = computed(() => {
  let filtered = [...testimonials.value];

  // Apply status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(t => t.status === selectedStatus.value);
  }

  // Apply event type filter
  if (selectedEventType.value) {
    filtered = filtered.filter(t => t.eventType === selectedEventType.value);
  }

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(t => 
      t.content.toLowerCase().includes(query) ||
      t.userName.toLowerCase().includes(query)
    );
  }

  // Apply sorting
  switch (sortBy.value) {
    case 'date-desc':
      filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
      break;
    case 'date-asc':
      filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
      break;
    case 'rating-desc':
      filtered.sort((a, b) => b.rating - a.rating);
      break;
    case 'rating-asc':
      filtered.sort((a, b) => a.rating - b.rating);
      break;
  }

  return filtered;
});

// Methods
const openTestimonial = (testimonial) => {
  selectedTestimonial.value = testimonial;
};

const closeTestimonial = () => {
  selectedTestimonial.value = null;
};

const openRejectModal = (testimonial) => {
  selectedTestimonial.value = testimonial;
  showRejectModal.value = true;
};

const closeRejectModal = () => {
  selectedTestimonial.value = null;
  showRejectModal.value = false;
  rejectReason.value = '';
};

const rejectTestimonial = async () => {
  if (!selectedTestimonial.value || !rejectReason.value) return;

  try {
    await reject(selectedTestimonial.value.id, rejectReason.value);
    closeRejectModal();
    await fetchTestimonials();
  } catch (error) {
    console.error('Error rejecting testimonial:', error);
  }
};

const confirmDelete = (testimonial) => {
  testimonialToDelete.value = testimonial;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  testimonialToDelete.value = null;
  showDeleteModal.value = false;
};

const deleteTestimonial = async () => {
  if (!testimonialToDelete.value) return;

  try {
    await remove(testimonialToDelete.value.id);
    closeDeleteModal();
    await fetchTestimonials();
  } catch (error) {
    console.error('Error deleting testimonial:', error);
  }
};

const loadMore = () => {
  loadMoreTestimonials({
    status: selectedStatus.value,
    eventType: selectedEventType.value
  });
};

const exportTestimonials = () => {
  // Implementation for exporting testimonials data
  // This could be a CSV or Excel export
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const openImage = (imageUrl) => {
  window.open(imageUrl, '_blank');
};

// Watch for filter changes
watch([selectedStatus, selectedEventType, sortBy], () => {
  fetchTestimonials(1, {
    status: selectedStatus.value,
    eventType: selectedEventType.value
  });
});

// Initial load
onMounted(() => {
  fetchTestimonials();
});
</script>

<style scoped>
.testimonials-management {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  color: var(--text-color);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-icon.pending {
  background: var(--warning-color);
}

.stat-icon.approved {
  background: var(--success-color);
}

.stat-icon.featured {
  background: var(--primary-color);
}

.stat-icon.total {
  background: var(--info-color);
}

.stat-info h3 {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin: 0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

/* Filter Section */
.filter-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 300px;
  position: relative;
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
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
}

.filter-controls {
  display: flex;
  gap: 1rem;
}

.filter-controls select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
}

/* Table Styles */
.table-container {
  background: var(--card-background);
  border-radius: 12px;
  overflow: auto;
  margin-bottom: 2rem;
}

.testimonials-table {
  width: 100%;
  border-collapse: collapse;
}

.testimonials-table th,
.testimonials-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.testimonials-table th {
  background: var(--card-background);
  color: var(--text-color);
  font-weight: 500;
  position: sticky;
  top: 0;
  z-index: 1;
}

.user-cell {
  min-width: 200px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-info img {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.rating-cell {
  min-width: 120px;
}

.rating {
  display: flex;
  gap: 0.25rem;
}

.rating i {
  color: var(--text-muted);
}

.rating i.active {
  color: var(--star-color, #ffd700);
}

.content-cell {
  min-width: 300px;
  max-width: 400px;
}

.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0;
}

.view-more {
  color: var(--primary-color);
  background: none;
  border: none;
  padding: 0;
  font-size: 0.875rem;
  cursor: pointer;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  text-transform: capitalize;
}

.status-badge.pending {
  background: var(--warning-color);
  color: white;
}

.status-badge.approved {
  background: var(--success-color);
  color: white;
}

.status-badge.rejected {
  background: var(--danger-color);
  color: white;
}

.actions-cell {
  min-width: 120px;
  display: flex;
  gap: 0.5rem;
}

.actions-cell button {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.approve-btn {
  background: var(--success-color);
  color: white;
  border: none;
}

.approve-btn:hover:not(:disabled) {
  background: var(--success-hover);
}

.reject-btn {
  background: var(--danger-color);
  color: white;
  border: none;
}

.reject-btn:hover:not(:disabled) {
  background: var(--danger-hover);
}

.feature-btn {
  background: none;
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
}

.feature-btn:hover:not(:disabled) {
  background: var(--primary-color);
  color: white;
}

.feature-btn.featured {
  background: var(--primary-color);
  color: white;
}

.delete-btn {
  background: none;
  border: 1px solid var(--danger-color);
  color: var(--danger-color);
}

.delete-btn:hover:not(:disabled) {
  background: var(--danger-color);
  color: white;
}

/* Modal Styles */
.testimonial-details {
  display: grid;
  gap: 1.5rem;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-header img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.testimonial-content {
  line-height: 1.6;
  color: var(--text-color);
}

.testimonial-images {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 1rem;
}

.testimonial-images img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s;
}

.testimonial-images img:hover {
  transform: scale(1.05);
}

.meta-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.reject-form {
  display: grid;
  gap: 1rem;
}

.reject-form textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  resize: vertical;
  min-height: 100px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.modal-actions button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.secondary-btn {
  background: none;
  border: 1px solid var(--border-color);
  color: var(--text-color);
}

.primary-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.primary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 0;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

/* Export Button */
.export-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.export-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.export-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .testimonials-management {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .filter-section {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-controls {
    width: 100%;
    flex-direction: column;
  }

  .filter-controls select {
    width: 100%;
  }

  .actions-cell {
    flex-wrap: wrap;
  }

  .actions-cell button {
    flex: 1;
  }
}
</style> 