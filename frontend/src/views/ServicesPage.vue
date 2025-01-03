<template>
  <div class="services-page">
    <!-- Hero Section -->
    <section class="services-hero">
      <div class="hero-content">
        <h1>Our Services</h1>
        <p>Discover our comprehensive event planning services tailored to your needs</p>
      </div>
    </section>

    <!-- Services Overview -->
    <section class="services-overview">
      <div class="service-categories">
        <button
          v-for="category in categories"
          :key="category.id"
          class="category-btn"
          :class="{ active: activeCategory === category.id }"
          @click="activeCategory = category.id"
        >
          <i :class="category.icon"></i>
          {{ category.name }}
        </button>
      </div>

      <div class="services-grid">
        <div
          v-for="service in filteredServices"
          :key="service.id"
          class="service-card"
          :class="{ featured: service.featured }"
        >
          <div class="service-image">
            <img :src="service.image" :alt="service.name" />
            <div v-if="service.featured" class="featured-badge">
              <i class="fas fa-star"></i>
              Popular Choice
            </div>
          </div>

          <div class="service-content">
            <h3>{{ service.name }}</h3>
            <p class="service-description">{{ service.description }}</p>
            
            <div class="service-features">
              <h4>What's Included:</h4>
              <ul>
                <li v-for="feature in service.features" :key="feature">
                  <i class="fas fa-check"></i>
                  {{ feature }}
                </li>
              </ul>
            </div>

            <div class="service-price">
              <div class="price-info">
                <span class="price">₱{{ formatPrice(service.price) }}</span>
                <span class="unit">{{ service.unit }}</span>
              </div>
              <router-link :to="'/book?service=' + service.id" class="book-btn">
                Book Now
                <i class="fas fa-arrow-right"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Additional Services -->
    <section class="additional-services">
      <h2>Additional Services</h2>
      <p>Enhance your event with our premium add-ons</p>

      <div class="addons-grid">
        <div
          v-for="addon in addons"
          :key="addon.id"
          class="addon-card"
        >
          <div class="addon-icon">
            <i :class="addon.icon"></i>
          </div>
          <h3>{{ addon.name }}</h3>
          <p>{{ addon.description }}</p>
          <div class="addon-price">
            From ₱{{ formatPrice(addon.price) }}
          </div>
        </div>
      </div>
    </section>

    <!-- Process Section -->
    <section class="process-section">
      <h2>How It Works</h2>
      <p>Our simple process to create your perfect event</p>

      <div class="process-steps">
        <div
          v-for="step in processSteps"
          :key="step.id"
          class="process-step"
        >
          <div class="step-number">{{ step.id }}</div>
          <div class="step-content">
            <h3>{{ step.title }}</h3>
            <p>{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="faq-grid">
        <div
          v-for="faq in faqs"
          :key="faq.id"
          class="faq-item"
          :class="{ active: activeFaq === faq.id }"
          @click="toggleFaq(faq.id)"
        >
          <div class="faq-header">
            <h3>{{ faq.question }}</h3>
            <i class="fas" :class="activeFaq === faq.id ? 'fa-minus' : 'fa-plus'"></i>
          </div>
          <div class="faq-answer" v-show="activeFaq === faq.id">
            {{ faq.answer }}
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="services-cta">
      <div class="cta-content">
        <h2>Ready to Start Planning?</h2>
        <p>Let's create an unforgettable event together</p>
        <div class="cta-buttons">
          <router-link to="/book" class="cta-primary">
            Book Now
            <i class="fas fa-arrow-right"></i>
          </router-link>
          <router-link to="/contact" class="cta-secondary">
            Contact Us
            <i class="fas fa-envelope"></i>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const activeCategory = ref('all');
const activeFaq = ref(null);

const categories = [
  { id: 'all', name: 'All Services', icon: 'fas fa-th-large' },
  { id: 'wedding', name: 'Weddings', icon: 'fas fa-rings' },
  { id: 'corporate', name: 'Corporate', icon: 'fas fa-briefcase' },
  { id: 'social', name: 'Social Events', icon: 'fas fa-glass-cheers' },
  { id: 'themed', name: 'Themed Parties', icon: 'fas fa-hat-wizard' },
];

const services = [
  {
    id: 1,
    category: 'wedding',
    name: 'Classic Wedding Package',
    description: 'A comprehensive wedding planning service for your special day.',
    image: '@/assets/services/wedding-classic.jpg',
    price: 150000,
    unit: 'per event',
    featured: true,
    features: [
      'Full wedding planning and coordination',
      'Venue selection and booking',
      'Vendor management',
      'Timeline planning',
      'Day-of coordination',
      'Setup and breakdown',
    ],
  },
  // Add more services...
];

const addons = [
  {
    id: 1,
    name: 'Photography',
    description: 'Professional photography services to capture your special moments.',
    icon: 'fas fa-camera',
    price: 25000,
  },
  {
    id: 2,
    name: 'Videography',
    description: 'Cinematic video coverage of your event.',
    icon: 'fas fa-video',
    price: 35000,
  },
  {
    id: 3,
    name: 'Live Band',
    description: 'Live music entertainment for your event.',
    icon: 'fas fa-music',
    price: 30000,
  },
  {
    id: 4,
    name: 'Photo Booth',
    description: 'Fun photo booth with props and instant prints.',
    icon: 'fas fa-camera-retro',
    price: 15000,
  },
];

const processSteps = [
  {
    id: 1,
    title: 'Initial Consultation',
    description: 'We meet to discuss your vision, preferences, and requirements.',
  },
  {
    id: 2,
    title: 'Proposal & Planning',
    description: 'We create a detailed proposal and timeline based on your needs.',
  },
  {
    id: 3,
    title: 'Vendor Selection',
    description: 'We help you choose and coordinate with the best vendors.',
  },
  {
    id: 4,
    title: 'Event Execution',
    description: 'We ensure everything runs smoothly on your special day.',
  },
];

const faqs = [
  {
    id: 1,
    question: 'How far in advance should I book your services?',
    answer: 'We recommend booking at least 6-8 months in advance for weddings and 3-4 months for other events to ensure availability and adequate planning time.',
  },
  {
    id: 2,
    question: 'What is your payment policy?',
    answer: 'We require a 50% deposit to secure your date, with the remaining balance due 2 weeks before the event.',
  },
  {
    id: 3,
    question: 'Can you work with my budget?',
    answer: 'Yes, we offer customizable packages and can work with you to create a plan that fits your budget while maintaining quality.',
  },
  {
    id: 4,
    question: 'Do you handle destination events?',
    answer: 'Yes, we can plan and coordinate events throughout the Philippines and internationally.',
  },
];

const filteredServices = computed(() => {
  if (activeCategory.value === 'all') return services;
  return services.filter(service => service.category === activeCategory.value);
});

const formatPrice = (price) => {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const toggleFaq = (id) => {
  activeFaq.value = activeFaq.value === id ? null : id;
};
</script>

<style scoped>
.services-page {
  min-height: 100vh;
  background: var(--background-color);
}

/* Hero Section */
.services-hero {
  height: 400px;
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
    url('@/assets/services-hero.jpg');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white;
  padding: 2rem;
}

.hero-content h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.hero-content p {
  font-size: 1.2rem;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto;
}

/* Services Overview */
.services-overview {
  padding: 4rem 2rem;
}

.overview-header {
  text-align: center;
  margin-bottom: 3rem;
}

.overview-header h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.overview-header p {
  color: var(--text-muted);
  max-width: 700px;
  margin: 0 auto;
}

.categories {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}

.category-btn {
  padding: 0.75rem 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.category-btn:hover,
.category-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.service-card {
  background: var(--card-background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: transform 0.3s;
  position: relative;
  border: 1px solid var(--border-color);
}

.service-card:hover {
  transform: translateY(-5px);
}

.service-card.featured {
  border: 2px solid var(--primary-color);
}

.service-image {
  position: relative;
  height: 200px;
}

.service-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.featured-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: var(--primary-color);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.service-content {
  padding: 1.5rem;
}

.service-content h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.service-description {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.service-features {
  margin-bottom: 1.5rem;
}

.service-features h4 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
}

.service-features ul {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.service-features li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
}

.service-features li i {
  color: var(--success-color);
}

.service-price {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.price-info {
  display: flex;
  flex-direction: column;
}

.price {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-color);
}

.unit {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.book-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: background-color 0.2s;
}

.book-btn:hover {
  background: var(--primary-hover);
}

/* Additional Services */
.additional-services {
  padding: 4rem 2rem;
  background: var(--card-background);
  text-align: center;
  border-top: 1px solid var(--border-color);
}

.additional-services h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.additional-services > p {
  color: var(--text-muted);
  margin-bottom: 3rem;
}

.addons-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.addon-card {
  padding: 2rem;
  background: var(--background-color);
  border-radius: 12px;
  text-align: center;
  transition: transform 0.3s;
  border: 1px solid var(--border-color);
}

.addon-card:hover {
  transform: translateY(-5px);
}

.addon-icon {
  width: 64px;
  height: 64px;
  background: var(--primary-light);
  color: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 1.5rem;
}

.addon-card h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 0.75rem;
}

.addon-card p {
  color: var(--text-muted);
  margin-bottom: 1rem;
}

.addon-price {
  font-weight: 600;
  color: var(--primary-color);
}

/* Process Section */
.process-section {
  padding: 4rem 2rem;
  text-align: center;
  background: var(--background-color);
}

.process-section h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.process-section > p {
  color: var(--text-muted);
  margin-bottom: 3rem;
}

.process-steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.process-step {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  text-align: left;
}

.step-number {
  width: 40px;
  height: 40px;
  background: var(--primary-light);
  color: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 600;
  flex-shrink: 0;
}

.step-content h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.step-content p {
  color: var(--text-muted);
}

/* FAQ Section */
.faq-section {
  padding: 4rem 2rem;
  background: var(--card-background);
  border-top: 1px solid var(--border-color);
}

.faq-section h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  text-align: center;
  margin-bottom: 3rem;
}

.faq-grid {
  max-width: 800px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.faq-item {
  background: var(--background-color);
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid var(--border-color);
}

.faq-header {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.faq-header h3 {
  font-size: 1.1rem;
  color: var(--text-color);
}

.faq-answer {
  padding: 0 1.5rem 1.5rem;
  color: var(--text-muted);
}

/* CTA Section */
.services-cta {
  padding: 6rem 2rem;
  background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
    url('@/assets/cta-background.jpg');
  background-size: cover;
  background-position: center;
  text-align: center;
  color: white;
}

.cta-content h2 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.2rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.cta-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.cta-primary,
.cta-secondary {
  padding: 1rem 2rem;
  border-radius: 8px;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all 0.3s;
}

.cta-primary {
  background: var(--primary-color);
  color: white;
}

.cta-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.cta-secondary {
  background: transparent;
  color: white;
  border: 2px solid white;
}

.cta-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .services-hero {
    height: 300px;
  }

  .hero-content h1 {
    font-size: 2rem;
  }

  .services-overview,
  .additional-services,
  .process-section,
  .faq-section,
  .services-cta {
    padding: 3rem 1rem;
  }

  .category-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .services-grid {
    grid-template-columns: 1fr;
  }

  .cta-buttons {
    flex-direction: column;
  }

  .cta-primary,
  .cta-secondary {
    width: 100%;
    justify-content: center;
  }
}
</style> 