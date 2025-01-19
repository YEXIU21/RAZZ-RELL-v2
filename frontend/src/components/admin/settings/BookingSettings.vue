<template>
  <div class="settings-section">
    <h2>Booking Settings</h2>
    <p class="section-description">
      Configure booking rules and policies
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="form-row">
        <div class="form-group">
          <label>Minimum Advance Booking Days</label>
          <input
            type="number"
            v-model="form.minAdvanceBookingDays"
            min="0"
            max="365"
          />
          <span class="help-text">
            Minimum number of days in advance a booking can be made
          </span>
        </div>

        <div class="form-group">
          <label>Maximum Advance Booking Days</label>
          <input
            type="number"
            v-model="form.maxAdvanceBookingDays"
            min="1"
            max="730"
          />
          <span class="help-text">
            Maximum number of days in advance a booking can be made
          </span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Deposit Percentage</label>
          <div class="input-with-suffix">
            <input
              type="number"
              v-model="form.depositPercentage"
              min="0"
              max="100"
            />
            <span class="suffix">%</span>
          </div>
          <span class="help-text">
            Required deposit percentage of the total booking amount
          </span>
        </div>

        <div class="form-group">
          <label>Refund Percentage</label>
          <div class="input-with-suffix">
            <input
              type="number"
              v-model="form.refundPercentage"
              min="0"
              max="100"
            />
            <span class="suffix">%</span>
          </div>
          <span class="help-text">
            Percentage of deposit to be refunded on cancellation
          </span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Cancellation Period</label>
          <div class="input-with-suffix">
            <input
              type="number"
              v-model="form.cancellationPeriodDays"
              min="1"
              max="90"
            />
            <span class="suffix">days</span>
          </div>
          <span class="help-text">
            Number of days before the event when cancellation is allowed
          </span>
        </div>

        <div class="form-group">
          <label>Maximum Installments</label>
          <input
            type="number"
            v-model="form.maxInstallments"
            min="1"
            max="12"
            :disabled="!form.allowInstallments"
          />
          <span class="help-text">
            Maximum number of installment payments allowed
          </span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input
              type="checkbox"
              v-model="form.allowInstallments"
            />
            Allow Installment Payments
          </label>
          <span class="help-text">
            Enable payment in installments for bookings
          </span>
        </div>

        <div class="form-group checkbox-group">
          <label class="checkbox-label">
            <input
              type="checkbox"
              v-model="form.requirePhoneVerification"
            />
            Require Phone Verification
          </label>
          <span class="help-text">
            Require phone number verification for bookings
          </span>
        </div>
      </div>

      <div class="form-group checkbox-group">
        <label class="checkbox-label">
          <input
            type="checkbox"
            v-model="form.requireEmailVerification"
          />
          Require Email Verification
        </label>
        <span class="help-text">
          Require email verification for bookings
        </span>
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
  minAdvanceBookingDays: 7,
  maxAdvanceBookingDays: 180,
  depositPercentage: 30,
  cancellationPeriodDays: 14,
  refundPercentage: 80,
  allowInstallments: true,
  maxInstallments: 3,
  requirePhoneVerification: true,
  requireEmailVerification: true
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('booking', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset booking settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('booking');
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

.input-with-suffix {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.input-with-suffix input {
  flex: 1;
}

.suffix {
  color: var(--text-muted);
  font-size: 0.875rem;
  padding-right: 0.5rem;
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