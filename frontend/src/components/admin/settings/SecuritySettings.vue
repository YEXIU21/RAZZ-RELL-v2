<template>
  <div class="settings-section">
    <h2>Security Settings</h2>
    <p class="section-description">
      Configure security and authentication settings
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="settings-group">
        <h3>Session Management</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Session Timeout (minutes)</label>
            <input
              type="number"
              v-model="form.sessionTimeout"
              min="5"
              max="1440"
            />
            <span class="help-text">
              Time in minutes before an inactive session expires
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.allowRememberMe"
              />
              Allow "Remember Me"
            </label>
            <span class="help-text">
              Allow users to stay logged in across browser sessions
            </span>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Login Security</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Maximum Login Attempts</label>
            <input
              type="number"
              v-model="form.maxLoginAttempts"
              min="1"
              max="10"
            />
            <span class="help-text">
              Number of failed login attempts before account lockout
            </span>
          </div>

          <div class="form-group">
            <label>Lockout Duration (minutes)</label>
            <input
              type="number"
              v-model="form.lockoutDuration"
              min="5"
              max="1440"
            />
            <span class="help-text">
              Duration of account lockout after failed login attempts
            </span>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Password Policy</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Minimum Password Length</label>
            <input
              type="number"
              v-model="form.passwordMinLength"
              min="6"
              max="32"
            />
            <span class="help-text">
              Minimum number of characters required for passwords
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.requireSpecialChars"
              />
              Require Special Characters
            </label>
            <span class="help-text">
              Require passwords to include special characters
            </span>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Two-Factor Authentication</h3>
        <div class="form-row">
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.require2FA"
              />
              Require 2FA
            </label>
            <span class="help-text">
              Require two-factor authentication for all users
            </span>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Social Authentication</h3>
        <div class="form-row">
          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.allowSocialLogin"
              />
              Allow Social Login
            </label>
            <span class="help-text">
              Enable login with social media accounts
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
  sessionTimeout: 60,
  maxLoginAttempts: 5,
  lockoutDuration: 30,
  passwordMinLength: 8,
  requireSpecialChars: true,
  require2FA: false,
  allowRememberMe: true,
  allowSocialLogin: true
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('security', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset security settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('security');
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

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

.form-group input[type="number"] {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--text-color);
}

.help-text {
  font-size: 0.875rem;
  color: var(--text-muted);
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

@media (max-width: 768px) {
  .settings-section {
    padding: 1.5rem;
  }

  .form-row {
    grid-template-columns: 1fr;
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