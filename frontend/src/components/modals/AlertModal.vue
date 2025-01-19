<template>
  <div class="alert-modal">
    <div class="modal-content">
      <h2>{{ title }}</h2>
      <p>{{ message }}</p>
      <button @click="$emit('close')" class="close-btn">Close</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AlertModal',
  props: {
    title: {
      type: String,
      default: 'Alert'
    },
    message: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.alert-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  background: var(--card-background);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: var(--shadow-lg);
  max-width: 500px;
  width: 90%;
  text-align: center;
}

h2 {
  color: var(--text-color);
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 1rem 0;
}

p {
  color: var(--text-secondary);
  font-size: 1rem;
  line-height: 1.5;
  margin: 0 0 1.5rem 0;
}

.close-btn {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

/* Dark mode specific styles */
:root[data-theme="dark"] {
  .modal-content {
    border: 1px solid var(--border-color);
  }
}
</style> 