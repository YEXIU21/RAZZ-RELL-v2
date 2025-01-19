<template>
  <div class="settings-page">
    <main class="settings-content">
      <div class="page-header">
        <h1>Settings</h1>
      </div>

      <div class="settings-grid">
        <!-- General Settings -->
        <div class="settings-section">
          <h2>
            <i class="fas fa-sliders-h"></i>
            General Settings
          </h2>
          <div class="settings-form">
            <div class="form-group">
              <label>Site Name</label>
              <input type="text" v-model="settings.siteName" placeholder="Enter site name">
            </div>
            <div class="form-group">
              <label>Contact Email</label>
              <input type="email" v-model="settings.contactEmail" placeholder="Enter contact email">
            </div>
            <div class="form-group">
              <label>Timezone</label>
              <select v-model="settings.timezone">
                <option value="UTC+8">Philippines (UTC+8)</option>
                <!-- Add more timezone options as needed -->
              </select>
            </div>
          </div>
        </div>

        <!-- Security Settings -->
        <div class="settings-section">
          <h2>
            <i class="fas fa-shield-alt"></i>
            Security Settings
          </h2>
          <div class="settings-form">
            <div class="form-group">
              <label>Minimum Password Length</label>
              <input type="number" v-model="settings.minPasswordLength" min="8">
            </div>
            <div class="form-group">
              <label>Session Timeout (minutes)</label>
              <input type="number" v-model="settings.sessionTimeout" min="15">
            </div>
            <div class="form-group">
              <div class="toggle-setting">
                <span>Two-Factor Authentication</span>
                <label class="switch">
                  <input type="checkbox" v-model="settings.twoFactorAuth">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Notification Settings -->
        <div class="settings-section">
          <h2>
            <i class="fas fa-bell"></i>
            Notification Settings
          </h2>
          <div class="settings-form">
            <div class="form-group">
              <div class="toggle-setting">
                <span>Email Notifications</span>
                <label class="switch">
                  <input type="checkbox" v-model="settings.emailNotifications">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <div class="toggle-setting">
                <span>Booking Notifications</span>
                <label class="switch">
                  <input type="checkbox" v-model="settings.bookingNotifications">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Backup Settings -->
        <div class="settings-section">
          <h2>
            <i class="fas fa-database"></i>
            Backup Settings
          </h2>
          <div class="settings-form">
            <div class="form-group">
              <label>Backup Frequency</label>
              <select v-model="settings.backupFrequency">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>
            <div class="form-group">
              <button class="backup-btn" @click="initiateBackup">
                <i class="fas fa-download"></i>
                Backup Now
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="settings-actions">
        <button class="cancel-btn" @click="resetSettings">Reset Changes</button>
        <button class="save-btn" @click="saveSettings">
          <i class="fas fa-save"></i>
          Save Settings
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import Swal from 'sweetalert2'

const { token } = useAuth()

const settings = ref({
  siteName: 'Razzrell Events',
  contactEmail: '',
  timezone: 'UTC+8',
  minPasswordLength: 8,
  sessionTimeout: 30,
  twoFactorAuth: false,
  emailNotifications: true,
  bookingNotifications: true,
  backupFrequency: 'daily'
})

const saveSettings = async () => {
  try {
    // API call to save settings would go here
    await Swal.fire({
      icon: 'success',
      title: 'Settings Saved',
      text: 'Your settings have been updated successfully.',
      confirmButtonColor: '#3b82f6'
    })
  } catch (error) {
    console.error('Error saving settings:', error)
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save settings. Please try again.',
      confirmButtonColor: '#3b82f6'
    })
  }
}

const resetSettings = () => {
  // Reset settings to default values
  // This would typically fetch from the backend
  Swal.fire({
    title: 'Reset Settings?',
    text: 'This will reset all settings to their default values.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, reset settings'
  }).then((result) => {
    if (result.isConfirmed) {
      // Reset logic would go here
      Swal.fire({
        icon: 'success',
        title: 'Settings Reset',
        text: 'All settings have been reset to default values.',
        confirmButtonColor: '#3b82f6'
      })
    }
  })
}

const initiateBackup = () => {
  Swal.fire({
    title: 'Backup in Progress',
    text: 'Please wait while we create your backup...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })

  // Simulate backup process
  setTimeout(() => {
    Swal.fire({
      icon: 'success',
      title: 'Backup Complete',
      text: 'Your backup has been created successfully.',
      confirmButtonColor: '#3b82f6'
    })
  }, 2000)
}
</script>

<style scoped>
.settings-page {
  padding: 24px;
  background: #f9fafb;
  min-height: 100vh;
}

.settings-content {
  flex: 1;
  padding: 2rem;
  margin-left: 250px;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: #111827;
  margin-bottom: 1rem;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.settings-section {
  background: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.settings-section h2 {
  font-size: 1.2rem;
  color: #111827;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.settings-section h2 i {
  color: #3b82f6;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: #374151;
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.toggle-setting {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  background-color: #e5e7eb;
  transition: 0.4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #3b82f6;
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.backup-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.backup-btn:hover {
  background-color: #2563eb;
}

.settings-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background-color: var(--gray-600);
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-btn:hover {
  background-color: var(--gray-700);
}

.save-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.save-btn:hover {
  background-color: var(--primary-hover);
}

@media (max-width: 768px) {
  .settings-content {
    margin-left: 0;
    padding: 1rem;
  }

  .settings-grid {
    grid-template-columns: 1fr;
  }

  .settings-actions {
    flex-direction: column;
  }

  .cancel-btn,
  .save-btn {
    width: 100%;
  }
}
</style>