<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>{{ title }}</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="icon-container" :class="type">
          <i class="fas" :class="iconClass"></i>
        </div>
        <p class="message">{{ message }}</p>
      </div>

      <div class="modal-actions">
        <button class="cancel-btn" @click="$emit('close')"> {{ cancelText }} </button>
        <button class="confirm-btn" :class="type" @click="$emit('confirm')" > {{ confirmText }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  }
});

const iconClass = computed(() => {
  const icons = {
    warning: 'fa-exclamation-triangle',
    danger: 'fa-exclamation-circle',
    info: 'fa-info-circle',
    success: 'fa-check-circle'
  };
  return icons[props.type];
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--overlay-background);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.5rem;
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--text-color);
}

.modal-body {
  text-align: center;
  margin-bottom: 2rem;
}

.icon-container {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.icon-container.warning {
  background: var(--warning-light);
  color: var(--warning-color);
}

.icon-container.danger {
  background: var(--error-light);
  color: var(--error-color);
}

.icon-container.info {
  background: var(--info-light);
  color: var(--info-color);
}

.icon-container.success {
  background: var(--success-light);
  color: var(--success-color);
}

.message {
  color: var(--text-color);
  font-size: 1.1rem;
  line-height: 1.5;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.cancel-btn, .confirm-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.cancel-btn {
  background: var(--secondary-color);
  color: white;
}

.confirm-btn {
  color: white;
}

.confirm-btn.warning {
  background: var(--warning-color);
}

.confirm-btn.danger {
  background: var(--error-color);
}

.confirm-btn.info {
  background: var(--info-color);
}

.confirm-btn.success {
  background: var(--success-color);
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1.5rem;
  }

  .modal-actions {
    flex-direction: column;
  }

  .cancel-btn, .confirm-btn {
    width: 100%;
  }
}
</style> 