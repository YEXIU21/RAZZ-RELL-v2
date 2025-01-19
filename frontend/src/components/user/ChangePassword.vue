<template>
  <div class="change-password-modal" v-if="isOpen">
    <div class="modal-content">
      <h2>Change Password</h2>
      <form @submit.prevent="handleSubmit" class="password-form">
        <div class="form-group">
          <label>Current Password:</label>
          <input 
            type="password" 
            v-model="formData.currentPassword"
            required
          />
        </div>

        <div class="form-group">
          <label>New Password:</label>
          <input 
            type="password" 
            v-model="formData.newPassword"
            required
            minlength="8"
          />
        </div>

        <div class="form-group">
          <label>Confirm New Password:</label>
          <input 
            type="password" 
            v-model="formData.confirmPassword"
            required
          />
        </div>

        <div class="button-group">
          <button type="submit" class="success-btn">Update Password</button>
          <button type="button" class="danger-btn" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuth } from '@/composables/useAuth';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close', 'success']);
const { token } = useAuth();

const formData = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const handleSubmit = async () => {
  try {
    // Validate passwords match
    if (formData.value.newPassword !== formData.value.confirmPassword) {
      throw new Error('New passwords do not match');
    }

    const response = await fetch('http://localhost:3000/api/user/change-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': token.value
      },
      body: JSON.stringify({
        currentPassword: formData.value.currentPassword,
        newPassword: formData.value.newPassword
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to change password');
    }

    // Reset form
    formData.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    };

    // Emit success
    emit('success', 'Password changed successfully');
  } catch (error) {
    alert(error.message);
  }
};
</script>

<style scoped>
.change-password-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1100;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
}

h2 {
  margin-bottom: 1.5rem;
  color: #333;
  text-align: center;
}

.password-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  color: #333;
  font-weight: 500;
}

input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
}

.button-group {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.success-btn, .danger-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
}

.success-btn {
  background-color: var(--success-color);
  color: white;
}

.success-btn:hover {
  background-color: var(--success-hover);
}

.danger-btn {
  background-color: var(--error-color);
  color: white;
}

.danger-btn:hover {
  background-color: var(--error-hover);
}

@media (max-width: 600px) {
  .modal-content {
    width: 95%;
    padding: 1.5rem;
  }

  .button-group {
    flex-direction: column;
  }
}
</style> 