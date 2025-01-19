<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Export Transactions</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Date Range</label>
          <div class="date-inputs">
            <div class="date-field">
              <label>From</label>
              <input 
                type="date" 
                v-model="startDate"
                :max="endDate"
              />
            </div>
            <div class="date-field">
              <label>To</label>
              <input 
                type="date" 
                v-model="endDate"
                :min="startDate"
              />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Export Format</label>
          <div class="format-options">
            <label class="format-option">
              <input 
                type="radio" 
                v-model="format" 
                value="csv"
              />
              <span class="format-label">
                <i class="fas fa-file-csv"></i>
                CSV
              </span>
            </label>
            <label class="format-option">
              <input 
                type="radio" 
                v-model="format" 
                value="excel"
              />
              <span class="format-label">
                <i class="fas fa-file-excel"></i>
                Excel
              </span>
            </label>
            <label class="format-option">
              <input 
                type="radio" 
                v-model="format" 
                value="pdf"
              />
              <span class="format-label">
                <i class="fas fa-file-pdf"></i>
                PDF
              </span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label>Include Fields</label>
          <div class="field-options">
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="transactionId"
              />
              Transaction ID
            </label>
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="customerName"
              />
              Customer Name
            </label>
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="amount"
              />
              Amount
            </label>
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="method"
              />
              Payment Method
            </label>
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="status"
              />
              Status
            </label>
            <label class="field-option">
              <input 
                type="checkbox" 
                v-model="selectedFields" 
                value="date"
              />
              Date
            </label>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          class="btn-export"
          :disabled="!isValid || isExporting"
          @click="exportTransactions"
        >
          <i class="fas fa-spinner fa-spin" v-if="isExporting"></i>
          {{ isExporting ? 'Exporting...' : 'Export' }}
        </button>
        <button class="btn-cancel" @click="$emit('close')">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';

const emit = defineEmits(['close']);
const { get } = useApi();
const { showNotification } = useNotifications();

const startDate = ref('');
const endDate = ref('');
const format = ref('csv');
const selectedFields = ref(['transactionId', 'customerName', 'amount', 'method', 'status', 'date']);
const isExporting = ref(false);

const isValid = computed(() => {
  return startDate.value && endDate.value && selectedFields.value.length > 0;
});

const exportTransactions = async () => {
  try {
    isExporting.value = true;

    const response = await get('/analytics/payments/export', {
      params: {
        startDate: startDate.value,
        endDate: endDate.value,
        format: format.value,
        fields: selectedFields.value
      },
      responseType: 'blob'
    });

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transactions_${startDate.value}_${endDate.value}.${format.value}`);
    document.body.appendChild(link);
    link.click();
    link.remove();

    showNotification({
      type: 'success',
      message: 'Transactions exported successfully'
    });

    emit('close');
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to export transactions'
    });
  } finally {
    isExporting.value = false;
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
  width: 90%;
  max-width: 500px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: var(--hover-color);
  color: var(--text-color);
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group > label {
  display: block;
  margin-bottom: 0.75rem;
  color: var(--text-color);
  font-weight: 500;
}

.date-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.date-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.date-field label {
  font-size: 0.9rem;
  color: var(--text-muted);
}

input[type="date"] {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

.format-options {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.format-option {
  cursor: pointer;
}

.format-option input[type="radio"] {
  display: none;
}

.format-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.format-label i {
  font-size: 1.5rem;
  color: var(--text-muted);
}

.format-option input[type="radio"]:checked + .format-label {
  border-color: var(--primary-color);
  background: var(--primary-color-light);
}

.format-option input[type="radio"]:checked + .format-label i {
  color: var(--primary-color);
}

.field-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.75rem;
}

.field-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.field-option input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: var(--primary-color);
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  gap: 1rem;
}

.btn-export, .btn-cancel {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-export {
  background: var(--primary-color);
  color: white;
}

.btn-export:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-cancel {
  background: var(--danger-color);
  color: white;
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%;
  }

  .format-options {
    grid-template-columns: 1fr;
  }

  .field-options {
    grid-template-columns: 1fr;
  }
}
</style> 