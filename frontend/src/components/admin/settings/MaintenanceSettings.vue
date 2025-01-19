<template>
  <div class="settings-section">
    <h2>Maintenance Settings</h2>
    <p class="section-description">
      Configure system maintenance and backup settings
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="settings-group">
        <h3>Maintenance Mode</h3>
        <div class="maintenance-mode">
          <div class="mode-toggle">
            <label class="switch">
              <input
                type="checkbox"
                v-model="form.maintenanceMode"
              />
              <span class="slider"></span>
            </label>
            <div class="mode-info">
              <h4>Enable Maintenance Mode</h4>
              <p>Display maintenance page to visitors</p>
            </div>
          </div>

          <div 
            class="mode-settings"
            v-show="form.maintenanceMode"
          >
            <div class="form-group">
              <label>Maintenance Message</label>
              <textarea
                v-model="form.maintenanceMessage"
                placeholder="Enter message to display during maintenance"
                rows="3"
              ></textarea>
              <span class="help-text">
                Message that will be shown to users during maintenance
              </span>
            </div>

            <div class="form-group">
              <label>Allowed IP Addresses</label>
              <div class="ip-list">
                <div 
                  v-for="(ip, index) in form.allowedIPs" 
                  :key="index"
                  class="ip-item"
                >
                  <input
                    type="text"
                    v-model="form.allowedIPs[index]"
                    placeholder="Enter IP address"
                  />
                  <button 
                    type="button"
                    class="remove-btn"
                    @click="removeIP(index)"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <button 
                  type="button"
                  class="add-btn"
                  @click="addIP"
                >
                  <i class="fas fa-plus"></i>
                  Add IP Address
                </button>
              </div>
              <span class="help-text">
                IP addresses that can access the site during maintenance
              </span>
            </div>

            <div class="form-group">
              <label>Scheduled Maintenance</label>
              <input
                type="datetime-local"
                v-model="form.scheduledMaintenance"
              />
              <span class="help-text">
                Schedule when maintenance mode should be automatically enabled
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Backup Settings</h3>
        <div class="backup-settings">
          <div class="form-row">
            <div class="form-group checkbox-group">
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.backupEnabled"
                />
                Enable Automatic Backups
              </label>
              <span class="help-text">
                Automatically backup system data
              </span>
            </div>

            <div class="form-group">
              <label>Backup Frequency</label>
              <select 
                v-model="form.backupFrequency"
                :disabled="!form.backupEnabled"
              >
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
              <span class="help-text">
                How often backups should be created
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Backup Retention (days)</label>
              <input
                type="number"
                v-model="form.backupRetentionDays"
                min="1"
                max="365"
                :disabled="!form.backupEnabled"
              />
              <span class="help-text">
                Number of days to keep backups
              </span>
            </div>

            <div class="form-group">
              <label>Log Retention (days)</label>
              <input
                type="number"
                v-model="form.logRetentionDays"
                min="1"
                max="365"
              />
              <span class="help-text">
                Number of days to keep system logs
              </span>
            </div>
          </div>

          <div class="backup-actions">
            <button 
              type="button"
              class="action-btn"
              @click="createBackup"
              :disabled="isCreatingBackup"
            >
              <i class="fas fa-download"></i>
              <span>Create Backup Now</span>
              <i class="fas fa-spinner fa-spin" v-if="isCreatingBackup"></i>
            </button>

            <button 
              type="button"
              class="action-btn"
              @click="cleanupBackups"
              :disabled="isCleaningBackups"
            >
              <i class="fas fa-broom"></i>
              <span>Cleanup Old Backups</span>
              <i class="fas fa-spinner fa-spin" v-if="isCleaningBackups"></i>
            </button>
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

const isCreatingBackup = ref(false);
const isCleaningBackups = ref(false);

const form = ref({
  maintenanceMode: false,
  maintenanceMessage: '',
  allowedIPs: [],
  scheduledMaintenance: null,
  backupEnabled: true,
  backupFrequency: 'daily',
  backupRetentionDays: 30,
  logRetentionDays: 90
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('maintenance', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset maintenance settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('maintenance');
      form.value = { ...defaults };
      emit('update:modelValue', { ...defaults });
    } catch (error) {
      console.error('Error resetting settings:', error);
    }
  }
};

const addIP = () => {
  form.value.allowedIPs.push('');
};

const removeIP = (index) => {
  form.value.allowedIPs.splice(index, 1);
};

const createBackup = async () => {
  isCreatingBackup.value = true;
  try {
    // API call to create backup
    await new Promise(resolve => setTimeout(resolve, 2000)); // Simulated delay
    showNotification({
      type: 'success',
      message: 'Backup created successfully'
    });
  } catch (error) {
    console.error('Error creating backup:', error);
    showNotification({
      type: 'error',
      message: 'Failed to create backup'
    });
  } finally {
    isCreatingBackup.value = false;
  }
};

const cleanupBackups = async () => {
  const confirmed = await showConfirmation({
    title: 'Cleanup Backups',
    message: 'Are you sure you want to delete old backups? This action cannot be undone.',
    confirmText: 'Cleanup',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    isCleaningBackups.value = true;
    try {
      // API call to cleanup backups
      await new Promise(resolve => setTimeout(resolve, 2000)); // Simulated delay
      showNotification({
        type: 'success',
        message: 'Old backups cleaned up successfully'
      });
    } catch (error) {
      console.error('Error cleaning up backups:', error);
      showNotification({
        type: 'error',
        message: 'Failed to cleanup backups'
      });
    } finally {
      isCleaningBackups.value = false;
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

h4 {
  font-size: 1.125rem;
  color: var(--text-color);
  margin-bottom: 0.25rem;
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

.maintenance-mode,
.backup-settings {
  background: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
}

.mode-toggle {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.mode-info p {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.mode-settings {
  display: grid;
  gap: 1.5rem;
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

.form-group input,
.form-group select,
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

.ip-list {
  display: grid;
  gap: 0.75rem;
}

.ip-item {
  display: flex;
  gap: 0.5rem;
}

.remove-btn {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--danger-color);
  cursor: pointer;
}

.add-btn {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--text-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
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

.backup-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.action-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  background: var(--background-color);
  border: 1px solid var(--border-color);
  color: var(--text-color);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--border-color);
  transition: 0.4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: var(--primary-color);
}

input:checked + .slider:before {
  transform: translateX(24px);
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

  .backup-actions {
    flex-direction: column;
  }

  .backup-actions button {
    width: 100%;
    justify-content: center;
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