<template>
  <section class="testimonials-section">
    <div class="section-header">
      <div class="header-content">
        <h2>What Our Clients Say</h2>
        <p>Real experiences from our valued customers</p>
      </div>
      <button 
        v-if="!showForm"
        class="add-testimonial-btn"
        @click="showForm = true"
      >
        <i class="fas fa-plus"></i>
        Share Your Experience
      </button>
    </div>

    <!-- Testimonial Form -->
    <testimonial-form
      v-if="showForm"
      @close="showForm = false"
      class="testimonial-form-container"
    />

    <!-- Featured Testimonials Carousel -->
    <div v-if="featuredTestimonials.length" class="featured-testimonials">
      <div class="carousel-container">
        <button 
          class="nav-btn prev"
          @click="previousSlide"
          :disabled="currentSlide === 0"
        >
          <i class="fas fa-chevron-left"></i>
        </button>

        <div class="carousel">
          <div 
            class="carousel-track"
            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
          >
            <testimonial-card
              v-for="testimonial in featuredTestimonials"
              :key="testimonial.id"
              :testimonial="testimonial"
              class="carousel-slide"
            />
          </div>
        </div>

        <button 
          class="nav-btn next"
          @click="nextSlide"
          :disabled="currentSlide === maxSlides"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <div class="carousel-indicators">
        <button
          v-for="index in maxSlides + 1"
          :key="index"
          class="indicator"
          :class="{ active: currentSlide === index - 1 }"
          @click="currentSlide = index - 1"
        ></button>
      </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          type="text"
          v-model="searchQuery"
          placeholder="Search testimonials..."
        />
      </div>

      <div class="filter-options">
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

    <!-- Testimonials Grid -->
    <div class="testimonials-grid">
      <testimonial-card
        v-for="testimonial in filteredTestimonials"
        :key="testimonial.id"
        :testimonial="testimonial"
      />
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
      <p v-if="searchQuery || selectedEventType">
        Try adjusting your filters or search query
      </p>
      <p v-else>
        Be the first to share your experience!
      </p>
      <button 
        class="add-testimonial-btn"
        @click="showForm = true"
      >
        Share Your Experience
      </button>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useTestimonials } from '@/composables/useTestimonials';
import TestimonialCard from './TestimonialCard.vue';
import TestimonialForm from './TestimonialForm.vue';

const {
  testimonials,
  isLoading,
  fetchTestimonials,
  hasMoreTestimonials,
  loadMore: loadMoreTestimonials
} = useTestimonials();

// UI State
const showForm = ref(false);
const searchQuery = ref('');
const selectedEventType = ref('');
const sortBy = ref('date-desc');
const currentSlide = ref(0);

// Computed Properties
const featuredTestimonials = computed(() => 
  testimonials.value.filter(t => t.featured)
);

const maxSlides = computed(() => 
  Math.max(0, Math.ceil(featuredTestimonials.value.length / 3) - 1)
);

const filteredTestimonials = computed(() => {
  let filtered = [...testimonials.value];

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
const previousSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--;
  }
};

const nextSlide = () => {
  if (currentSlide.value < maxSlides.value) {
    currentSlide.value++;
  }
};

const loadMore = () => {
  loadMoreTestimonials({
    eventType: selectedEventType.value
  });
};

// Watch for filter changes
watch([selectedEventType, sortBy], () => {
  fetchTestimonials(1, {
    eventType: selectedEventType.value
  });
});

// Auto-advance carousel
let carouselInterval;
const startCarousel = () => {
  carouselInterval = setInterval(() => {
    if (currentSlide.value < maxSlides.value) {
      currentSlide.value++;
    } else {
      currentSlide.value = 0;
    }
  }, 5000);
};

const stopCarousel = () => {
  clearInterval(carouselInterval);
};

onMounted(() => {
  fetchTestimonials();
  startCarousel();
});

// Clean up
onUnmounted(() => {
  stopCarousel();
});
</script>

<style scoped>
.testimonials-section {
  padding: 4rem 2rem;
  background: var(--background-color);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3rem;
}

.header-content h2 {
  font-size: 2rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.header-content p {
  color: var(--text-muted);
  font-size: 1.1rem;
}

.add-testimonial-btn {
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

.add-testimonial-btn:hover {
  background: var(--primary-hover);
}

.testimonial-form-container {
  margin-bottom: 3rem;
}

/* Featured Testimonials Carousel */
.featured-testimonials {
  margin-bottom: 3rem;
}

.carousel-container {
  position: relative;
  margin: 0 -1rem;
}

.carousel {
  overflow: hidden;
  padding: 0 1rem;
}

.carousel-track {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.carousel-slide {
  min-width: calc(33.333% - 2rem);
  margin: 0 1rem;
  flex-shrink: 0;
}

.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  z-index: 1;
}

.nav-btn:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-50%) scale(1.1);
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nav-btn.prev {
  left: -20px;
}

.nav-btn.next {
  right: -20px;
}

.carousel-indicators {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
}

.indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border-color);
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.indicator:hover,
.indicator.active {
  background: var(--primary-color);
  transform: scale(1.2);
}

/* Filter Controls */
.filter-controls {
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

.filter-options {
  display: flex;
  gap: 1rem;
}

.filter-options select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
}

/* Testimonials Grid */
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

/* Load More */
.load-more {
  text-align: center;
}

.load-more-btn {
  padding: 0.75rem 2rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.load-more-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.load-more-btn:disabled {
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

.empty-state p {
  margin-bottom: 1.5rem;
}

@media (max-width: 1024px) {
  .carousel-slide {
    min-width: calc(50% - 2rem);
  }
}

@media (max-width: 768px) {
  .testimonials-section {
    padding: 2rem 1rem;
  }

  .section-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .filter-controls {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-options {
    width: 100%;
  }

  .filter-options select {
    flex: 1;
  }

  .carousel-slide {
    min-width: calc(100% - 2rem);
  }
}
</style> 