<template>
  <div class="gallery-page">
    <header class="gallery-header">
      <div class="header-content">
        <h1>Our Portfolio</h1>
        <p>Browse through our collection of memorable events</p>
        <div class="header-decoration"></div>
      </div>
    </header>

    <!-- Filter Controls -->
    <div class="filter-section">
      <div class="filter-controls">
        <div class="category-filters">
          <button
            v-for="category in standardCategories"
            :key="category.value"
            class="filter-btn"
            :class="{ active: activeCategory === category.value }"
            @click="setCategory(category.value)"
          >
            <i :class="category.icon"></i>
            {{ category.label }}
          </button>
          
          <!-- Custom Event Types Section -->
          <div v-if="customEventTypes.length > 0" class="custom-events-dropdown">
            <button 
              class="filter-btn custom-events-btn"
              @click="toggleCustomEventsMenu"
            >
              <i class="fas fa-ellipsis-h"></i>
              Custom Events
            </button>
            <div v-if="showCustomEvents" class="custom-events-menu">
              <button
                v-for="eventType in customEventTypes"
                :key="eventType"
                class="custom-event-btn"
                :class="{ active: activeCategory === eventType }"
                @click="setCategory(eventType)"
              >
                <i class="fas fa-calendar-alt"></i>
                {{ eventType }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="view-controls">
        <button
          class="view-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="viewMode = 'grid'"
        >
          <i class="fas fa-th"></i>
          Grid
        </button>
        <button
          class="view-btn"
          :class="{ active: viewMode === 'masonry' }"
          @click="viewMode = 'masonry'"
        >
          <i class="fas fa-columns"></i>
          Masonry
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="loader"></div>
      <p>Loading amazing moments...</p>
    </div>

    <!-- Gallery Grid -->
    <div
      v-else
      class="gallery-container"
      :class="{ 'masonry-layout': viewMode === 'masonry' }"
    >
      <div
        v-for="item in filteredItems"
        :key="item.id"
        class="gallery-item"
        :class="{ 'has-album': item.images_url && Array.isArray(item.images_url) && item.images_url.length > 0 }"
        @click="openItem(item)"
      >
        <div class="item-thumbnail">
          <img :src="getImageUrl(item.main_image_url)" :alt="item.title" loading="lazy" />
          <div class="item-overlay">
            <div class="overlay-content">
              <h3>{{ item.title }}</h3>
              <p>{{ item.description }}</p>
              <div class="item-meta">
                <span class="event-date">
                  <i class="fas fa-calendar"></i>
                  {{ formatDate(item.created_at) }}
                </span>
                <span v-if="item.event_type" class="event-category">
                  <i class="fas fa-tag"></i>
                  {{ formatEventType(item.event_type) }}
                </span>
              </div>
            </div>
            <span v-if="item.images_url && Array.isArray(item.images_url) && item.images_url.length > 0" class="album-badge">
              <i class="fas fa-images"></i>
              {{ item.images_url.length }} Photos
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="selectedItem" class="lightbox" @click.self="closeLightbox">
      <div class="lightbox-content">
        <div class="lightbox-header">
          <h2>{{ selectedItem.title }}</h2>
          <button class="close-btn" @click="closeLightbox">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="lightbox-gallery">
          <!-- Main image -->
          <div class="main-image">
            <img v-if="currentImage" :src="getImageUrl(currentImage)" :alt="selectedItem.title" />
          </div>

          <!-- Thumbnails -->
          <div v-if="hasAlbum" class="thumbnails">
            <div
              v-for="(image, index) in allImages"
              :key="index"
              class="thumbnail"
              :class="{ active: currentImageIndex === index }"
              @click="setCurrentImage(index)"
            >
              <img :src="getImageUrl(image)" :alt="selectedItem.title" />
            </div>
          </div>
        </div>

        <div class="lightbox-info">
          <p class="description">{{ selectedItem.description }}</p>
          <div class="meta-info">
            <span class="date">
              <i class="fas fa-calendar"></i>
              {{ formatDate(selectedItem.created_at) }}
            </span>
            <span class="event-type" v-if="selectedItem.event_type">
              <i class="fas fa-tag"></i>
              {{ formatEventType(selectedItem.event_type) }}
            </span>
          </div>
        </div>

        <!-- Navigation -->
        <button
          class="nav-btn prev"
          @click="showPrevious"
          v-if="hasPrevious"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          class="nav-btn next"
          @click="showNext"
          v-if="hasNext"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const viewMode = ref('grid');
const activeCategory = ref('all');
const selectedItem = ref(null);
const currentImageIndex = ref(0);
const portfolios = ref([]);
const isLoading = ref(false);
const showCustomEvents = ref(false);
const customEventTypes = ref([]);

// Standard categories without the "Other" option
const standardCategories = ref([
  { label: 'All Events', value: 'all', icon: 'fas fa-globe' },
  { label: 'Wedding', value: 'Wedding', icon: 'fas fa-heart' },
  { label: 'Debut', value: 'Debut', icon: 'fas fa-star' },
  { label: 'Christening', value: 'Christening', icon: 'fas fa-baby' },
  { label: 'Party', value: 'Party', icon: 'fas fa-birthday-cake' }
]);

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `${import.meta.env.VITE_API_URL}/storage/${path}`;
};

const filteredItems = computed(() => {
  if (activeCategory.value === 'all') {
    return portfolios.value;
  }
  return portfolios.value.filter(item => item.event_type === activeCategory.value);
});

const hasAlbum = computed(() => {
  return selectedItem.value?.images_url && Array.isArray(selectedItem.value.images_url) && selectedItem.value.images_url.length > 0;
});

const allImages = computed(() => {
  if (!selectedItem.value) return [];
  const mainImage = selectedItem.value.main_image_url;
  const albumImages = selectedItem.value.images_url || [];
  return [mainImage, ...albumImages];
});

const currentImage = computed(() => {
  if (!allImages.value.length) return '';
  return allImages.value[currentImageIndex.value];
});

const hasPrevious = computed(() => {
  return currentImageIndex.value > 0;
});

const hasNext = computed(() => {
  return currentImageIndex.value < allImages.value.length - 1;
});

// Function to toggle custom events menu
const toggleCustomEventsMenu = () => {
  showCustomEvents.value = !showCustomEvents.value;
};

// Modified fetch portfolios function to collect custom event types
const fetchportfolios = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-portfolios`);
    portfolios.value = response.data.map(portfolio => ({
      ...portfolio,
      images_url: Array.isArray(portfolio.images_url) ? portfolio.images_url : []
    }));
    
    // Collect unique custom event types
    const standardEventTypes = standardCategories.value.map(cat => cat.value);
    const customTypes = new Set();
    
    portfolios.value.forEach(portfolio => {
      if (!standardEventTypes.includes(portfolio.event_type) && portfolio.event_type !== 'all') {
        customTypes.add(portfolio.event_type);
      }
    });
    
    customEventTypes.value = Array.from(customTypes);
    
  } catch (error) {
    console.error('Error fetching portfolios:', error);
  } finally {
    isLoading.value = false;
  }
};

const setCategory = (category) => {
  activeCategory.value = category;
};

const openItem = (item) => {
  selectedItem.value = item;
  currentImageIndex.value = 0;
  document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
  selectedItem.value = null;
  currentImageIndex.value = 0;
  document.body.style.overflow = '';
};

const setCurrentImage = (index) => {
  currentImageIndex.value = index;
};

const showPrevious = () => {
  if (hasPrevious.value) {
    currentImageIndex.value--;
  }
};

const showNext = () => {
  if (hasNext.value) {
    currentImageIndex.value++;
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatEventType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};

// Close custom events menu when clicking outside
onMounted(() => {
  document.addEventListener('click', (event) => {
    const dropdown = document.querySelector('.custom-events-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
      showCustomEvents.value = false;
    }
  });
  
  fetchportfolios();
});
</script>

<style scoped>
.gallery-page {
  padding-top: 60px;
  padding-left: 2rem;
  padding-right: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
  background: var(--background-color);
  color: var(--text-color);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.gallery-header {
  text-align: center;
  margin-bottom: 2rem;
  position: relative;
}

.header-content {
  position: relative;
  display: inline-block;
  padding: 1rem;
}

.header-decoration {
  position: absolute;
  width: 60px;
  height: 60px;
  border: 3px solid var(--primary-color);
  top: -20px;
  left: -20px;
  opacity: 0.3;
  animation: rotate 20s linear infinite;
}

.header-decoration::after {
  content: '';
  position: absolute;
  width: 60px;
  height: 60px;
  border: 3px solid var(--primary-color);
  bottom: -20px;
  right: -20px;
  opacity: 0.3;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.gallery-header h1 {
  font-size: 2.5rem;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
  font-weight: 600;
  transition: color 0.3s ease;
}

.gallery-header p {
  font-size: 1.1rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
  transition: color 0.3s ease;
}

.filter-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.filter-controls {
  flex: 1;
  background: var(--card-background);
  padding: 0.75rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px var(--shadow-color);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.category-filters {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.6rem 1.2rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.filter-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: translateY(-1px);
}

.filter-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.view-controls {
  display: flex;
  gap: 0.5rem;
  background: var(--card-background);
  padding: 0.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px var(--shadow-color);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.view-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease, color 0.3s ease;
}

.view-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: translateY(-1px);
}

.view-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.loading-state {
  text-align: center;
  padding: 4rem 0;
}

.loader {
  display: inline-block;
  width: 50px;
  height: 50px;
  border: 3px solid var(--border-color);
  border-radius: 50%;
  border-top-color: var(--primary-color);
  animation: spin 1s ease-in-out infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.gallery-container {
  display: grid;
  gap: 2rem;
  margin-bottom: 3rem;
  opacity: 0;
  animation: fadeIn 0.6s ease forwards;
}

@keyframes fadeIn {
  to { opacity: 1; }
}

.gallery-container:not(.masonry-layout) {
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
}

.masonry-layout {
  column-count: 3;
  column-gap: 2rem;
}

@media (max-width: 1200px) {
  .masonry-layout {
    column-count: 2;
  }
}

@media (max-width: 768px) {
  .masonry-layout {
    column-count: 1;
  }
  
  .gallery-container:not(.masonry-layout) {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
  
  .gallery-header h1 {
    font-size: 2.5rem;
  }
}

.gallery-item {
  break-inside: avoid;
  margin-bottom: 2rem;
  opacity: 0;
  transform: translateY(20px);
  animation: slideUp 0.6s ease forwards;
}

@keyframes slideUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.item-thumbnail {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  border-radius: 12px;
}

.item-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.item-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.7));
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 2rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.item-thumbnail:hover .item-overlay {
  opacity: 1;
}

.item-thumbnail:hover img {
  transform: scale(1.05);
}

.overlay-content {
  transform: translateY(20px);
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.item-thumbnail:hover .overlay-content {
  transform: translateY(0);
}

.item-overlay h3 {
  color: #ffffff;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.item-overlay p {
  color: #ffffff;
  font-size: 1rem;
  line-height: 1.6;
  margin-bottom: 1rem;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.item-meta {
  display: flex;
  gap: 1rem;
  color: #ffffff;
  font-size: 0.9rem;
}

.item-meta span {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.album-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: var(--card-background);
  color: var(--primary-color);
  padding: 0.6rem 1.2rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 4px 12px var(--shadow-color);
  transform: translateY(-10px);
  opacity: 0;
  transition: all 0.4s ease 0.2s, background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

.item-thumbnail:hover .album-badge {
  transform: translateY(0);
  opacity: 1;
}

.album-badge i {
  font-size: 1.1rem;
}

.event-type {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .lightbox-content {
    padding: 1rem;
  }

  .thumbnails {
    padding: 0.25rem;
  }

  .thumbnail {
    width: 60px;
    height: 60px;
  }
}

.custom-events-dropdown {
  position: relative;
  display: inline-block;
}

.custom-events-btn {
  white-space: nowrap;
}

.custom-events-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  background: var(--card-background);
  border-radius: 8px;
  box-shadow: 0 4px 12px var(--shadow-color);
  padding: 0.5rem;
  z-index: 100;
  min-width: 200px;
  animation: fadeIn 0.2s ease;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.custom-event-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.6rem 1rem;
  border: none;
  background: none;
  color: var(--text-color);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 6px;
  text-align: left;
}

.custom-event-btn:hover {
  background: rgba(64, 112, 244, 0.1);
  color: var(--primary-color, #4070f4);
}

.custom-event-btn.active {
  background: var(--primary-color, #4070f4);
  color: white;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.gallery-item h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #ffffff;
}

.gallery-item p {
  font-size: 1rem;
  color: #ffffff;
}

.lightbox-content h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #ffffff;
}

.lightbox-info .description {
  font-size: 1.1rem;
  color: #ffffff;
  line-height: 1.6;
}

.lightbox {
  position: fixed;
  inset: 0;
  background: var(--overlay-color);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
  transition: background-color 0.3s ease;
}

.lightbox-content {
  position: relative;
  width: 95%;
  max-width: 1400px;
  max-height: 95vh;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  background: var(--modal-background);
  border-radius: 12px;
  padding: 1.5rem;
  overflow-y: auto;
  transition: background-color 0.3s ease;
  margin: 1rem 0;
}

.lightbox-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5rem 1rem 1rem 1rem;
  position: relative;
}

.lightbox-header h2 {
  color: var(--text-color);
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
  transition: color 0.3s ease;
}

.close-btn {
  position: absolute;
  top: 5rem;
  right: 1rem;
  background: var(--card-background);
  border: none;
  color: var(--text-color);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease, background-color 0.3s ease, color 0.3s ease;
  backdrop-filter: blur(4px);
  z-index: 1000;
}

.close-btn i {
  font-size: 1.5rem;
}

.close-btn:hover {
  background: var(--card-background-hover);
  transform: rotate(90deg) scale(1.1);
}

.lightbox-gallery {
  position: relative;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.main-image {
  width: 100%;
  height: 55vh;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  border-radius: 16px;
  background: var(--card-background);
  transition: background-color 0.3s ease;
}

.main-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.thumbnails {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  padding: 1rem;
  margin-bottom: 1rem;
  background: var(--card-background);
  border-radius: 12px;
  scrollbar-width: thin;
  scrollbar-color: var(--scrollbar-thumb) transparent;
  transition: background-color 0.3s ease;
  min-height: 120px;
}

.thumbnails::-webkit-scrollbar {
  height: 6px;
}

.thumbnails::-webkit-scrollbar-track {
  background: transparent;
}

.thumbnails::-webkit-scrollbar-thumb {
  background-color: var(--scrollbar-thumb);
  border-radius: 6px;
}

.thumbnail {
  width: 100px;
  height: 100px;
  flex-shrink: 0;
  cursor: pointer;
  border: 2px solid transparent;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  opacity: 0.6;
}

.thumbnail:hover {
  opacity: 0.8;
}

.thumbnail.active {
  border-color: #4070f4;
  opacity: 1;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.lightbox-info {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  transition: background-color 0.3s ease;
}

.description {
  color: var(--text-color);
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 1rem;
  transition: color 0.3s ease;
}

.meta-info {
  display: flex;
  gap: 2rem;
  color: var(--text-color);
  transition: color 0.3s ease;
}

.meta-info span {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  color: var(--text-color);
  transition: color 0.3s ease;
}

.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: var(--card-background);
  color: var(--text-color);
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease, background-color 0.3s ease, color 0.3s ease;
  backdrop-filter: blur(4px);
  z-index: 2;
}

.nav-btn:hover {
  background: var(--card-background-hover);
  transform: translateY(-50%) scale(1.1);
}

.nav-btn.prev {
  left: -70px;
}

.nav-btn.next {
  right: -70px;
}

.nav-btn i {
  font-size: 1.5rem;
}

@media (max-width: 768px) {
  .lightbox {
    padding: 0;
  }

  .lightbox-content {
    width: 100%;
    height: 100vh;
    max-height: none;
    border-radius: 0;
    padding: 1rem;
  }

  .main-image {
    height: 50vh;
  }

  .thumbnail {
    width: 80px;
    height: 80px;
  }

  .nav-btn {
    top: auto;
    bottom: 1rem;
    transform: none;
  }

  .nav-btn:hover {
    transform: scale(1.1);
  }

  .nav-btn.prev {
    left: 1rem;
  }

  .nav-btn.next {
    right: 1rem;
  }

  .close-btn {
    top: 0.5rem;
    right: 0.5rem;
  }
}

.has-album {
  position: relative;
}

.album-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: var(--card-background);
  color: var(--primary-color);
  padding: 0.6rem 1.2rem;
  border-radius: 20px;
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 
    0 4px 12px rgba(0, 0, 0, 0.2),
    0 0 1px rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(4px);
  transform: translateY(-10px);
  opacity: 0;
  transition: all 0.4s ease 0.2s, background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
}

.item-thumbnail:hover .album-badge {
  transform: translateY(0);
  opacity: 1;
}

.album-badge i {
  font-size: 1.1rem;
}

.event-type {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .lightbox-content {
    padding: 1rem;
  }

  .thumbnails {
    padding: 0.25rem;
  }

  .thumbnail {
    width: 60px;
    height: 60px;
  }
}

.custom-events-dropdown {
  position: relative;
  display: inline-block;
}

.custom-events-btn {
  white-space: nowrap;
}

.custom-events-menu {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  background: var(--card-background);
  border-radius: 8px;
  box-shadow: 0 4px 12px var(--shadow-color);
  padding: 0.5rem;
  z-index: 100;
  min-width: 200px;
  animation: fadeIn 0.2s ease;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.custom-event-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.6rem 1rem;
  border: none;
  background: none;
  color: var(--text-color);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 6px;
  text-align: left;
}

.custom-event-btn:hover {
  background: rgba(64, 112, 244, 0.1);
  color: var(--primary-color, #4070f4);
}

.custom-event-btn.active {
  background: var(--primary-color, #4070f4);
  color: white;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.gallery-item h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #ffffff;
}

.gallery-item p {
  font-size: 1rem;
  color: #ffffff;
}

.lightbox-content h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #ffffff;
}

.lightbox-info .description {
  font-size: 1.1rem;
  color: #ffffff;
  line-height: 1.6;
}
</style> 