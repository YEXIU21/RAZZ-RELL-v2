<template>
  <div class="settings-section">
    <h2>Appearance Settings</h2>
    <p class="section-description">
      Customize the look and feel of your website
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="settings-group">
        <h3>Theme</h3>
        <div class="theme-selector">
          <label 
            class="theme-option"
            :class="{ active: form.theme === 'light' }"
          >
            <input
              type="radio"
              v-model="form.theme"
              value="light"
            />
            <div class="theme-preview light-theme">
              <i class="fas fa-sun"></i>
              <span>Light</span>
            </div>
          </label>

          <label 
            class="theme-option"
            :class="{ active: form.theme === 'dark' }"
          >
            <input
              type="radio"
              v-model="form.theme"
              value="dark"
            />
            <div class="theme-preview dark-theme">
              <i class="fas fa-moon"></i>
              <span>Dark</span>
            </div>
          </label>
        </div>
      </div>

      <div class="settings-group">
        <h3>Colors</h3>
        <div class="color-grid">
          <div class="form-group">
            <label>Primary Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.primaryColor"
              />
              <input
                type="text"
                v-model="form.primaryColor"
                placeholder="#000000"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Secondary Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.secondaryColor"
              />
              <input
                type="text"
                v-model="form.secondaryColor"
                placeholder="#000000"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Accent Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.accentColor"
              />
              <input
                type="text"
                v-model="form.accentColor"
                placeholder="#000000"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Success Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.successColor"
              />
              <input
                type="text"
                v-model="form.successColor"
                placeholder="#000000"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Warning Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.warningColor"
              />
              <input
                type="text"
                v-model="form.warningColor"
                placeholder="#000000"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Danger Color</label>
            <div class="color-input">
              <input
                type="color"
                v-model="form.dangerColor"
              />
              <input
                type="text"
                v-model="form.dangerColor"
                placeholder="#000000"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Typography</h3>
        <div class="form-group">
          <label>Font Family</label>
          <select v-model="form.fontFamily">
            <option value="Inter, sans-serif">Inter</option>
            <option value="Roboto, sans-serif">Roboto</option>
            <option value="Open Sans, sans-serif">Open Sans</option>
            <option value="Montserrat, sans-serif">Montserrat</option>
            <option value="Poppins, sans-serif">Poppins</option>
          </select>
          <span class="help-text">
            Select the primary font family for your website
          </span>
        </div>
      </div>

      <div class="settings-group">
        <h3>Interface</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Border Radius</label>
            <div class="input-with-suffix">
              <input
                type="text"
                v-model="form.borderRadius"
                placeholder="8px"
              />
            </div>
            <span class="help-text">
              Border radius for UI elements (e.g., 8px, 0.5rem)
            </span>
          </div>

          <div class="form-group checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.enableAnimations"
              />
              Enable Animations
            </label>
            <span class="help-text">
              Enable UI animations and transitions
            </span>
          </div>
        </div>
      </div>

      <div class="preview-section">
        <h3>Preview</h3>
        <div 
          class="preview-container"
          :style="{
            '--preview-primary-color': form.primaryColor,
            '--preview-secondary-color': form.secondaryColor,
            '--preview-accent-color': form.accentColor,
            '--preview-success-color': form.successColor,
            '--preview-warning-color': form.warningColor,
            '--preview-danger-color': form.dangerColor,
            '--preview-font-family': form.fontFamily,
            '--preview-border-radius': form.borderRadius,
          }"
        >
          <div class="preview-header">
            <h4>Sample Header</h4>
            <p>This is how your content will look</p>
          </div>
          <div class="preview-content">
            <button class="preview-btn primary">Primary Button</button>
            <button class="preview-btn secondary">Secondary Button</button>
            <div class="preview-alert success">Success Message</div>
            <div class="preview-alert warning">Warning Message</div>
            <div class="preview-alert danger">Error Message</div>
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
  theme: 'light',
  primaryColor: '#4f46e5',
  secondaryColor: '#3b82f6',
  accentColor: '#10b981',
  dangerColor: '#ef4444',
  warningColor: '#f59e0b',
  successColor: '#22c55e',
  fontFamily: 'Inter, sans-serif',
  borderRadius: '8px',
  enableAnimations: true
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('appearance', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset appearance settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('appearance');
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

.theme-selector {
  display: flex;
  gap: 1rem;
}

.theme-option {
  flex: 1;
  cursor: pointer;
}

.theme-option input[type="radio"] {
  display: none;
}

.theme-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  border: 2px solid var(--border-color);
  border-radius: 12px;
  transition: all 0.2s ease;
}

.theme-preview i {
  font-size: 1.5rem;
}

.theme-option.active .theme-preview {
  border-color: var(--primary-color);
  background: var(--primary-color-light);
}

.light-theme {
  background: #ffffff;
  color: #1a1a1a;
}

.dark-theme {
  background: #1a1a1a;
  color: #ffffff;
}

.color-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.color-input {
  display: flex;
  gap: 0.75rem;
}

.color-input input[type="color"] {
  width: 50px;
  height: 38px;
  padding: 0;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
}

.color-input input[type="text"] {
  flex: 1;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--background-color);
  color: var(--text-color);
}

.form-group select {
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

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
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

.preview-section {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.preview-container {
  background: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
  font-family: var(--preview-font-family);
}

.preview-header {
  margin-bottom: 1.5rem;
}

.preview-header h4 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.preview-content {
  display: grid;
  gap: 1rem;
}

.preview-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--preview-border-radius);
  font-weight: 500;
  cursor: pointer;
}

.preview-btn.primary {
  background: var(--preview-primary-color);
  color: white;
}

.preview-btn.secondary {
  background: var(--preview-secondary-color);
  color: white;
}

.preview-alert {
  padding: 1rem;
  border-radius: var(--preview-border-radius);
  font-weight: 500;
}

.preview-alert.success {
  background: var(--preview-success-color);
  color: white;
}

.preview-alert.warning {
  background: var(--preview-warning-color);
  color: white;
}

.preview-alert.danger {
  background: var(--preview-danger-color);
  color: white;
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

  .theme-selector {
    flex-direction: column;
  }

  .color-grid,
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