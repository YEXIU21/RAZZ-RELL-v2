<template>
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-section">
        <div class="footer-logo">
          <img src="@/assets/logo.png" alt="Razz Rell Events" />
        </div>
        <p class="footer-description">
          Creating unforgettable moments for your special occasions. Professional event planning services for weddings, debuts, christenings, and parties.
        </p>
      </div>

      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul class="footer-links">
          <li><router-link to="/packages">Packages</router-link></li>
          <li><router-link to="/gallery">Gallery</router-link></li>
          <li><router-link to="/about">About Us</router-link></li>
          <li><router-link to="/contact">Contact</router-link></li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Contact Info</h3>
        <ul class="contact-info">
          <li>
            <i class="fas fa-map-marker-alt"></i>
            123 Event Street, Manila, Philippines
          </li>
          <li>
            <i class="fas fa-phone"></i>
            +63 912 345 6789
          </li>
          <li>
            <i class="fas fa-envelope"></i>
            info@razzrellevents.com
          </li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Newsletter</h3>
        <p>Subscribe to get updates on our latest events and special offers!</p>
        <form @submit.prevent="handleSubscribe" class="newsletter-form">
          <div class="input-group">
            <input 
              type="email" 
              v-model="email" 
              placeholder="Enter your email"
              required
            />
            <button type="submit" :disabled="isLoading">
              <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
              <span v-else>Subscribe</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="social-links">
        <a href="#" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-facebook"></i>
        </a>
        <a href="#" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#" target="_blank" rel="noopener noreferrer">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
      <p class="copyright">
        © {{ currentYear }} Razz Rell Events. All rights reserved.
      </p>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';

const { api } = useApi();
const { showNotification } = useNotifications();

const email = ref('');
const isLoading = ref(false);

const currentYear = computed(() => new Date().getFullYear());

const handleSubscribe = async () => {
  try {
    isLoading.value = true;
    await api.post('/newsletter/subscribe', { email: email.value });
    showNotification('Successfully subscribed to newsletter!', 'success');
    email.value = '';
  } catch (error) {
    showNotification(
      error.response?.data?.message || 'Failed to subscribe to newsletter',
      'error'
    );
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.footer {
  background: var(--card-background);
  padding: 4rem 0 0;
  margin-top: 4rem;
  border-top: 1px solid var(--border-color);
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.footer-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.footer-logo img {
  height: 50px;
  width: auto;
  margin-bottom: 1rem;
}

.footer-description {
  color: var(--text-secondary);
  line-height: 1.6;
}

.footer-links {
  list-style: none;
  padding: 0;
}

.footer-links li {
  margin-bottom: 0.5rem;
}

.footer-links a {
  color: var(--text-color);
  text-decoration: none;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: var(--primary-color);
}

.contact-info {
  list-style: none;
  padding: 0;
}

.contact-info li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  color: var(--text-color);
}

.contact-info i {
  color: var(--primary-color);
  width: 20px;
}

.newsletter-form {
  margin-top: 1rem;
}

.input-group {
  display: flex;
  gap: 0.5rem;
}

.input-group input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--background-color);
  color: var(--text-color);
}

.input-group button {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.input-group button:hover:not(:disabled) {
  background: var(--primary-hover);
}

.input-group button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.footer-bottom {
  margin-top: 4rem;
  padding: 2rem;
  text-align: center;
  border-top: 1px solid var(--border-color);
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.social-links a {
  color: var(--text-color);
  font-size: 1.5rem;
  transition: color 0.2s;
}

.social-links a:hover {
  color: var(--primary-color);
}

.copyright {
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
  }

  .input-group {
    flex-direction: column;
  }

  .input-group button {
    width: 100%;
  }
}
</style> 