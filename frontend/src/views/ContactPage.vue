<template>
  <div class="contact-page">
    <header class="contact-header">
      <h1>Get in Touch</h1>
      <p>We'd love to hear from you. Let us help make your event extraordinary.</p>
    </header>

    <div class="contact-grid">
      <!-- Contact Form -->
      <div class="contact-form-container">
        <form @submit.prevent="handleSubmit" class="contact-form">
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">First Names</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="firstName"
                  v-model="formValues.firstName"
                  @blur="handleBlur('firstName')"
                  :class="{ 'error': getFieldError('firstName') }"
                  placeholder="Enter first name"
                />
              </div>
              <span class="error-message" v-if="getFieldError('firstName')">
                {{ getFieldError('firstName') }}
              </span>
            </div>

            <div class="form-group">
              <label for="lastName">Last Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="lastName"
                  v-model="formValues.lastName"
                  @blur="handleBlur('lastName')"
                  :class="{ 'error': getFieldError('lastName') }"
                  placeholder="Enter last name"
                />
              </div>
              <span class="error-message" v-if="getFieldError('lastName')">
                {{ getFieldError('lastName') }}
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input
                  type="email"
                  id="email"
                  v-model="formValues.email"
                  @blur="handleBlur('email')"
                  :class="{ 'error': getFieldError('email') }"
                  placeholder="Enter email"
                />
              </div>
              <span class="error-message" v-if="getFieldError('email')">
                {{ getFieldError('email') }}
              </span>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <i class="fas fa-phone"></i>
                <input
                  type="tel"
                  id="phone"
                  v-model="formValues.phone"
                  @blur="handleBlur('phone')"
                  :class="{ 'error': getFieldError('phone') }"
                  placeholder="Enter phone number"
                  maxlength="11"
                  pattern="[0-9]{11}"
                  @input="formValues.phone = formValues.phone.replace(/\D/g, '')"
                />
              </div>
              <span class="error-message" v-if="getFieldError('phone')">
                {{ getFieldError('phone') }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label for="eventType">Event Type</label>
            <div class="input-group">
              <i class="fas fa-calendar-alt"></i>
              <select
                id="eventType"
                v-model="formValues.eventType"
                @blur="handleBlur('eventType')"
                :class="{ 'error': getFieldError('eventType') }"
              >
                <option value="">Select event type</option>
                <option value="Wedding">Wedding</option>
                <option value="Debut">Debut</option>
                <option value="Christening">Christening</option>
                <option value="Party">Party</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <span class="error-message" v-if="getFieldError('eventType')">
              {{ getFieldError('eventType') }}
            </span>
          </div>

          <div class="form-group" v-if="formValues.eventType === 'Other'">
            <label for="otherEventType">Specify Event Type</label>
            <div class="input-group">
              <i class="fas fa-pencil-alt"></i>
              <input
                type="text"
                id="otherEventType"
                v-model="formValues.otherEventType"
                @blur="handleBlur('otherEventType')"
                :class="{ 'error': getFieldError('otherEventType') }"
                placeholder="Please specify your event type"
              />
            </div>
            <span class="error-message" v-if="getFieldError('otherEventType')">
              {{ getFieldError('otherEventType') }}
            </span>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <div class="input-group">
              <i class="fas fa-comment-alt"></i>
              <textarea
                id="message"
                v-model="formValues.message"
                @blur="handleBlur('message')"
                :class="{ 'error': getFieldError('message') }"
                placeholder="Tell us about your event"
                rows="5"
              ></textarea>
            </div>
            <span class="error-message" v-if="getFieldError('message')">
              {{ getFieldError('message') }}
            </span>
          </div>

          <button
            type="submit"
            class="submit-btn"
            :disabled="isSubmitting"
          >
            <span>{{ isSubmitting ? 'Sending...' : 'Send Message' }}</span>
          </button>
        </form>
      </div>

      <!-- Contact Info -->
      <div class="contact-info">
        <div class="info-card">
          <div class="info-header">
            <h2>Contact Information</h2>
            <p>Get in touch with us through any of these channels</p>
          </div>

          <div class="info-items">
            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <div class="info-content">
                <h3>Location</h3>
                <p>Camolinas Poblacion Cordova Cebu</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-phone"></i>
              </div>
              <div class="info-content">
                <h3>Phone</h3>
                <p>+63 912 345 6789</p>
                <p>+63 998 765 4321</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="info-content">
                <h3>Email</h3>
                <p>info@razzrellevents.com</p>
                <p>support@razzrellevents.com</p>
              </div>
            </div>

            <div class="info-item">
              <div class="info-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="info-content">
                <h3>Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 4:00 PM</p>
              </div>
            </div>
          </div>

          <div class="social-links">
            <a href="#" class="social-link">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="#" class="social-link">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link">
              <i class="fab fa-linkedin"></i>
            </a>
          </div>
        </div>

        <!-- Map -->
        <div class="map-container">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3925.5676766857187!2d123.95103595!3d10.25283333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDE1JzEwLjIiTiAxMjPCsDU3JzAzLjciRQ!5e0!3m2!1sen!2sph!4v1234567890"
            width="100%"
            height="300"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useValidation } from '@/composables/useValidation';
import { useNotifications } from '@/composables/useNotifications';
import { useAuth } from '@/composables/useAuth';
import { useLoading } from '@/composables/useLoading';
import { useApi } from '@/composables/useApi';
import axios from 'axios';
import Swal from 'sweetalert2';

// Create reactive form values
const formValues = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  eventType: '',
  otherEventType: '',
  message: ''
});

// Create error states
const errors = ref({});
const isSubmitting = ref(false);
const submitted = ref(false);

// Basic validation function
const validate = () => {
  const newErrors = {};
  
  if (!formValues.value.firstName) newErrors.firstName = 'First name is required';
  if (!formValues.value.lastName) newErrors.lastName = 'Last name is required';
  if (!formValues.value.email) {
    newErrors.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formValues.value.email)) {
    newErrors.email = 'Invalid email format';
  }
  if (!formValues.value.phone) {
    newErrors.phone = 'Phone number is required';
  } else if (!/^[0-9]{11}$/.test(formValues.value.phone)) {
    newErrors.phone = 'Phone number must be exactly 11 digits';
  }
  if (!formValues.value.eventType) newErrors.eventType = 'Event type is required';
  if (formValues.value.eventType === 'Other' && !formValues.value.otherEventType) {
    newErrors.otherEventType = 'Please specify the event type';
  }
  if (!formValues.value.message) newErrors.message = 'Message is required';

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

// Handle field blur for validation
const handleBlur = (field) => {
  const newErrors = { ...errors.value };
  
  switch (field) {
    case 'firstName':
      if (!formValues.value.firstName) newErrors.firstName = 'First name is required';
      else delete newErrors.firstName;
      break;
    case 'lastName':
      if (!formValues.value.lastName) newErrors.lastName = 'Last name is required';
      else delete newErrors.lastName;
      break;
    case 'email':
      if (!formValues.value.email) {
        newErrors.email = 'Email is required';
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formValues.value.email)) {
        newErrors.email = 'Invalid email format';
      } else {
        delete newErrors.email;
      }
      break;
    case 'phone':
      if (!formValues.value.phone) {
        newErrors.phone = 'Phone number is required';
      } else if (!/^[0-9]{11}$/.test(formValues.value.phone)) {
        newErrors.phone = 'Phone number must be exactly 11 digits';
      } else {
        delete newErrors.phone;
      }
      break;
    // Add other field validations as needed
  }
  
  errors.value = newErrors;
};

// Get field error
const getFieldError = (field) => {
  return submitted.value ? errors.value[field] : '';
};

const handleSubmit = async () => {
  try {
    submitted.value = true;
    if (!validate()) {
      await Swal.fire({
        title: 'Validation Error',
        text: 'Please fill in all required fields correctly.',
        icon: 'error',
        confirmButtonColor: '#4070f4'
      });
      return;
    }

    isSubmitting.value = true;

    // Ensure phone number is properly formatted
    const phoneNumber = formValues.value.phone.trim();
    if (!phoneNumber || phoneNumber.length !== 11) {
      throw new Error('Phone number must be exactly 11 digits');
    }

    const formData = {
      first_name: formValues.value.firstName.trim(),
      last_name: formValues.value.lastName.trim(),
      email: formValues.value.email.trim(),
      phone: phoneNumber,
      event_type: formValues.value.eventType === 'Other' ? formValues.value.otherEventType.trim() : formValues.value.eventType,
      message: formValues.value.message.trim()
    };

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/contact-us`, formData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    if (response.status === 200 || response.status === 201) {
      await Swal.fire({
        title: 'Success!',
        text: 'Your message has been sent successfully.',
        icon: 'success',
        confirmButtonColor: '#4070f4'
      });
      
      // Reset form
      formValues.value = {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        eventType: '',
        otherEventType: '',
        message: ''
      };
      errors.value = {};
      submitted.value = false;
    }
  } catch (error) {
    console.error('Error sending message:', error);
    let errorMessage = 'Failed to send message. Please try again.';
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.message) {
      errorMessage = error.message;
    }

    await Swal.fire({
      title: 'Error',
      text: errorMessage,
      icon: 'error',
      confirmButtonColor: '#4070f4'
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.contact-page {
  padding-top: 80px;
  padding-left: 2rem;
  padding-right: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  min-height: 100vh;
}

.contact-header {
  text-align: center;
  margin-bottom: 2rem;
}

.contact-header h1 {
  font-size: 2.5rem;
  color: var(--primary-color, #4070f4);
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.contact-header p {
  font-size: 1.1rem;
  color: var(--text-secondary, #666);
  margin-bottom: 1.5rem;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.contact-form-container {
  background: var(--card-background);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: var(--shadow-md);
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 1rem;
  color: var(--text-muted);
}

.input-group input,
.input-group select,
.input-group textarea {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: border-color 0.2s;
}

.input-group textarea {
  resize: vertical;
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
}

.input-group input.error,
.input-group select.error,
.input-group textarea.error {
  border-color: var(--error-color);
}

.error-message {
  font-size: 0.875rem;
  color: var(--error-color);
}

.submit-btn {
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.info-card {
  background: var(--card-background);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: var(--shadow-md);
}

.info-header {
  text-align: center;
  margin-bottom: 2rem;
}

.info-header h2 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.info-header p {
  color: var(--text-muted);
}

.info-items {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.info-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.info-content h3 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin-bottom: 0.25rem;
}

.info-content p {
  color: var(--text-muted);
  font-size: 0.95rem;
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);
}

.social-link {
  width: 40px;
  height: 40px;
  background: var(--input-background);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-color);
  font-size: 1.2rem;
  transition: all 0.3s;
  text-decoration: none;
}

.social-link:hover {
  background: var(--primary-color);
  color: white;
  transform: translateY(-2px);
}

.map-container {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
}

@media (max-width: 768px) {
  .contact-page {
    padding: 2rem 1rem;
  }

  .contact-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .contact-header h1 {
    font-size: 2rem;
  }

  .info-header h2 {
    font-size: 1.5rem;
  }
}
</style> 