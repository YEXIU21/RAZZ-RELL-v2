<template>
  <div class="slideshow-container">
    <div v-for="(slide, index) in slides" 
         :key="index" 
         class="slide" 
         :class="{ active: currentSlide === index }">
      <img :src="slide.image" :alt="slide.title" />
    </div>
    
    <div class="slideshow-navigation">
      <button class="nav-btn prev" @click="prevSlide">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button class="nav-btn next" @click="nextSlide">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    
    <div class="slideshow-dots">
      <button v-for="(_, index) in slides" 
              :key="index" 
              class="dot"
              :class="{ active: currentSlide === index }"
              @click="goToSlide(index)">
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  slides: {
    type: Array,
    required: true
  }
});

const currentSlide = ref(0);
let slideInterval = null;

const startSlideshow = () => {
  stopSlideshow();
  slideInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % props.slides.length;
  }, 5000);
};

const stopSlideshow = () => {
  if (slideInterval) {
    clearInterval(slideInterval);
    slideInterval = null;
  }
};

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % props.slides.length;
  startSlideshow();
};

const prevSlide = () => {
  currentSlide.value = currentSlide.value === 0 
    ? props.slides.length - 1 
    : currentSlide.value - 1;
  startSlideshow();
};

const goToSlide = (index) => {
  currentSlide.value = index;
  startSlideshow();
};

onMounted(() => {
  startSlideshow();
});

onBeforeUnmount(() => {
  stopSlideshow();
});
</script>

<style scoped>
.slideshow-container {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.5s ease-in-out;
}

.slide.active {
  opacity: 1;
}

.slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
}

.slideshow-navigation {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  transform: translateY(-50%);
  display: flex;
  justify-content: space-between;
  padding: 0 20px;
  z-index: 2;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.slideshow-container:hover .slideshow-navigation {
  opacity: 1;
  visibility: visible;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.4);
  transform: scale(1.1);
}

.slideshow-dots {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 10px;
  z-index: 2;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.slideshow-container:hover .slideshow-dots {
  opacity: 1;
  visibility: visible;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot.active {
  background: white;
  transform: scale(1.2);
}

.dot:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: scale(1.2);
}
</style> 