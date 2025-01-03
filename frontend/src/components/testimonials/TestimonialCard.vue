<template>
  <div class="testimonial-card" :class="{ 'admin-view': isAdmin }">
    <div class="testimonial-header">
      <div class="user-info">
        <img 
          :src="testimonial.userAvatar || '/default-avatar.png'" 
          :alt="testimonial.userName"
          class="user-avatar"
        />
        <div class="user-details">
          <h3 class="user-name">{{ testimonial.userName }}</h3>
          <p class="event-type">{{ testimonial.eventType }}</p>
        </div>
      </div>
      <div class="rating">
        <i 
          v-for="star in 5" 
          :key="star"
          class="fas fa-star"
          :class="{ active: star <= testimonial.rating }"
        ></i>
      </div>
    </div>

    <div class="testimonial-content">
      <p>{{ testimonial.content }}</p>
    </div>

    <div class="testimonial-footer">
      <div class="testimonial-date">
        {{ formatDate(testimonial.date) }}
      </div>

      <div v-if="testimonial.images?.length" class="testimonial-images">
        <img 
          v-for="(image, index) in testimonial.images" 
          :key="index"
          :src="image"
          :alt="'Event photo ' + (index + 1)"
          @click="openImage(image)"
        />
      </div>

      <div v-if="isAdmin" class="admin-actions">
        <template v-if="testimonial.status === 'pending'">
          <button 
            class="approve-btn"
            @click="$emit('approve', testimonial.id)"
            :disabled="isLoading"
          >
            <i class="fas fa-check"></i>
            Approve
          </button>
          <button 
            class="reject-btn"
            @click="$emit('reject', testimonial.id)"
            :disabled="isLoading"
          >
            <i class="fas fa-times"></i>
            Reject
          </button>
        </template>
        <button 
          v-if="testimonial.status === 'approved'"
          class="feature-btn"
          @click="$emit('toggle-feature', testimonial.id)"
          :class="{ featured: testimonial.featured }"
          :disabled="isLoading"
        >
          <i class="fas" :class="testimonial.featured ? 'fa-star' : 'fa-star-o'"></i>
          {{ testimonial.featured ? 'Featured' : 'Feature' }}
        </button>
        <button 
          class="delete-btn"
          @click="$emit('delete', testimonial.id)"
          :disabled="isLoading"
        >
          <i class="fas fa-trash"></i>
          Delete
        </button>
      </div>
    </div>

    <!-- Status Badge -->
    <div 
      v-if="isAdmin"
      class="status-badge"
      :class="testimonial.status"
    >
      {{ testimonial.status }}
    </div>

    <!-- Featured Badge -->
    <div 
      v-if="testimonial.featured"
      class="featured-badge"
    >
      <i class="fas fa-star"></i>
      Featured
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  testimonial: {
    type: Object,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['approve', 'reject', 'delete', 'toggle-feature']);

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
</script>

<style scoped>
.testimonial-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
  transition: transform 0.2s, box-shadow 0.2s;
}

.testimonial-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.testimonial-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.event-type {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin: 0;
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

.testimonial-content {
  color: var(--text-color);
  line-height: 1.6;
  margin-bottom: 1rem;
}

.testimonial-footer {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.testimonial-date {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.testimonial-images {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.testimonial-images img {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.2s;
}

.testimonial-images img:hover {
  transform: scale(1.05);
}

.admin-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.admin-actions button {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.admin-actions button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
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

.status-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
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

.featured-badge {
  position: absolute;
  top: -0.5rem;
  left: 1rem;
  background: var(--primary-color);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

@media (max-width: 640px) {
  .testimonial-header {
    flex-direction: column;
    gap: 1rem;
  }

  .rating {
    align-self: flex-start;
  }

  .admin-actions {
    flex-direction: column;
  }

  .admin-actions button {
    width: 100%;
    justify-content: center;
  }
}
</style> 