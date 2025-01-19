<template>
  <div class="settings-section">
    <h2>General Settings</h2>
    <p class="section-description">
      Configure basic information about your business
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="form-group">
        <label>Site Name</label>
        <input
          type="text"
          v-model="form.siteName"
          placeholder="Enter site name"
        />
      </div>

      <div class="form-group">
        <label>Site Description</label>
        <textarea
          v-model="form.siteDescription"
          placeholder="Enter site description"
          rows="3"
        ></textarea>
      </div>

      <div class="form-group">
        <label>Contact Email</label>
        <input
          type="email"
          v-model="form.contactEmail"
          placeholder="Enter contact email"
        />
      </div>

      <div class="form-group">
        <label>Contact Phone</label>
        <input
          type="tel"
          v-model="form.contactPhone"
          placeholder="Enter contact phone"
        />
      </div>

      <div class="form-group">
        <label>Business Address</label>
        <textarea
          v-model="form.address"
          placeholder="Enter business address"
          rows="3"
        ></textarea>
      </div>

      <div class="form-group">
        <label>Business Hours</label>
        <textarea
          v-model="form.businessHours"
          placeholder="Enter business hours"
          rows="3"
        ></textarea>
        <span class="help-text">
          Example: Mon-Fri: 9:00 AM - 6:00 PM, Sat: 10:00 AM - 4:00 PM, Sun: Closed
        </span>
      </div>

      <div class="form-group">
        <label>Social Media Links</label>
        <div class="social-links">
          <div class="social-input">
            <i class="fab fa-facebook"></i>
            <input
              type="url"
              v-model="form.socialMedia.facebook"
              placeholder="Facebook URL"
            />
          </div>

          <div class="social-input">
            <i class="fab fa-instagram"></i>
            <input
              type="url"
              v-model="form.socialMedia.instagram"
              placeholder="Instagram URL"
            />
          </div>

          <div class="social-input">
            <i class="fab fa-twitter"></i>
            <input
              type="url"
              v-model="form.socialMedia.twitter"
              placeholder="Twitter URL"
            />
          </div>

          <div class="social-input">
            <i class="fab fa-linkedin"></i>
            <input
              type="url"
              v-model="form.socialMedia.linkedin"
              placeholder="LinkedIn URL"
            />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button 
          type="button" 
          class="secondary-btn"
          @click="resetForm"
        >
          Reset
        </button>
        <button 
          type="submit" 
          class="primary-btn"
          :disabled="isLoading"
        >
          <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSettings } from '@/composables/useSettings';
import { useConfirmation } from '@/composables/useConfirmation';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const { isLoading, updateSettings, resetSettings } = useSettings();
const { showConfirmation } = useConfirmation();

const form = ref({
  siteName: '',
  siteDescription: '',
  contactEmail: '',
  contactPhone: '',
  address: '',
  businessHours: '',
  socialMedia: {
    facebook: '',
    instagram: '',
    twitter: '',
    linkedin: ''
  }
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('general', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset general settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('general');
      form.value = { ...defaults };
      emit('update:modelValue', { ...defaults });
    } catch (error) {
      console.error('Error resetting settings:', error);
    }
  }
};

// Watch for external changes
watch(() => props.modelValue, initForm, { deep: true });

// Initialize form
onMounted(initForm);
</script>

<style scoped>
.settings-section {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
}

h2 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.section-description {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.settings-form {
  display: grid;
  gap: 1.5rem;
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

.form-group input,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--text-color);
}

.form-group textarea {
  resize: vertical;
}

.help-text {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.social-links {
  display: grid;
  gap: 1rem;
}

.social-input {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
}

.social-input i {
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
}

.social-input input {
  flex: 1;
  border: none;
  padding: 0.25rem 0;
  background: transparent;
}

.social-input input:focus {
  outline: none;
}

.fa-facebook { color: #1877f2; }
.fa-instagram { color: #e4405f; }
.fa-twitter { color: #1da1f2; }
.fa-linkedin { color: #0077b5; }

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.form-actions button {
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
  transition: background-color 0.3s ease;
}

.secondary-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.primary-btn {
  background: #3b82f6;
  color: white;
  border: none;
  transition: background-color 0.3s ease;
}

.primary-btn:hover:not(:disabled) {
  background: #2563eb;
}

.primary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .settings-section {
    padding: 1.5rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
    justify-content: center;
  }
}
</style> 