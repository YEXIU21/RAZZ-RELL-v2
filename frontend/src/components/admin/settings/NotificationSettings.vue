<template>
  <div class="settings-section">
    <h2>Notification Settings</h2>
    <p class="section-description">
      Configure notification preferences and channels
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="settings-group">
        <h3>Notification Channels</h3>
        <div class="form-row">
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.emailNotifications"
              />
              Email Notifications
            </label>
            <span class="help-text">
              Send notifications via email
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.smsNotifications"
              />
              SMS Notifications
            </label>
            <span class="help-text">
              Send notifications via SMS
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.adminEmailNotifications"
              />
              Admin Email Notifications
            </label>
            <span class="help-text">
              Send notifications to admin email addresses
            </span>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Notification Types</h3>
        <div class="notification-types-grid">
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.bookingConfirmation"
              />
              Booking Confirmation
            </label>
            <span class="help-text">
              Send confirmation when a booking is made
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.bookingReminder"
              />
              Booking Reminder
            </label>
            <span class="help-text">
              Send reminder before the event date
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.paymentReminder"
              />
              Payment Reminder
            </label>
            <span class="help-text">
              Send reminder for upcoming payments
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.eventReminder"
              />
              Event Reminder
            </label>
            <span class="help-text">
              Send reminder on the day of the event
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.feedbackRequest"
              />
              Feedback Request
            </label>
            <span class="help-text">
              Send request for feedback after the event
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.marketingEmails"
              />
              Marketing Emails
            </label>
            <span class="help-text">
              Send promotional offers and updates
            </span>
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

    <div class="test-notifications">
      <h3>Test Notifications</h3>
      <p class="help-text">
        Send test notifications to verify your settings
      </p>
      <div class="test-buttons">
        <button 
          class="test-btn"
          @click="testEmail"
          :disabled="!form.emailNotifications || isTestingEmail"
        >
          <i class="fas fa-envelope"></i>
          <span>Test Email</span>
          <i class="fas fa-spinner fa-spin" v-if="isTestingEmail"></i>
        </button>
        <button 
          class="test-btn"
          @click="testSMS"
          :disabled="!form.smsNotifications || isTestingSMS"
        >
          <i class="fas fa-sms"></i>
          <span>Test SMS</span>
          <i class="fas fa-spinner fa-spin" v-if="isTestingSMS"></i>
        </button>
      </div>
    </div>
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

const { 
  isLoading, 
  updateSettings, 
  resetSettings,
  testEmailSettings,
  testSMSSettings
} = useSettings();

const { showConfirmation } = useConfirmation();

const isTestingEmail = ref(false);
const isTestingSMS = ref(false);

const form = ref({
  emailNotifications: true,
  smsNotifications: true,
  adminEmailNotifications: true,
  bookingConfirmation: true,
  bookingReminder: true,
  paymentReminder: true,
  eventReminder: true,
  feedbackRequest: true,
  marketingEmails: true
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('notifications', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset notification settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('notifications');
      form.value = { ...defaults };
      emit('update:modelValue', { ...defaults });
    } catch (error) {
      console.error('Error resetting settings:', error);
    }
  }
};

const testEmail = async () => {
  isTestingEmail.value = true;
  try {
    await testEmailSettings();
  } catch (error) {
    console.error('Error testing email:', error);
  } finally {
    isTestingEmail.value = false;
  }
};

const testSMS = async () => {
  isTestingSMS.value = true;
  try {
    await testSMSSettings();
  } catch (error) {
    console.error('Error testing SMS:', error);
  } finally {
    isTestingSMS.value = false;
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

h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.section-description {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.settings-form {
  display: grid;
  gap: 2rem;
}

.settings-group {
  display: grid;
  gap: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.notification-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.checkbox-group {
  margin-top: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  color: var(--text-color);
  font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  border: 2px solid var(--border-color);
  border-radius: 4px;
  cursor: pointer;
}

.help-text {
  font-size: 0.875rem;
  color: var(--text-muted);
}

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

.test-notifications {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);
}

.test-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.test-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  background: var(--background-color);
  border: 1px solid var(--border-color);
  color: var(--text-color);
}

.test-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.test-btn i {
  font-size: 1.125rem;
}

@media (max-width: 768px) {
  .settings-section {
    padding: 1.5rem;
  }

  .form-row,
  .notification-types-grid {
    grid-template-columns: 1fr;
  }

  .form-actions,
  .test-buttons {
    flex-direction: column;
  }

  .form-actions button,
  .test-btn {
    width: 100%;
    justify-content: center;
  }
}
</style> 