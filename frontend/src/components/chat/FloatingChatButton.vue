<template>
  <div class="floating-chat-container">
    <button 
      class="floating-chat-button"
      :class="{ 'has-unread': unreadCount > 0 }"
      @click="$emit('toggle-chat')"
    >
      <div v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</div>
      <i :class="isOpen ? 'fas fa-times' : 'fas fa-comments'" style="font-size: 24px;"></i>
    </button>
  </div>
</template>

<script setup>
defineProps({
  unreadCount: {
    type: Number,
    default: 0
  },
  isOpen: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-chat']);
</script>

<style scoped>
.floating-chat-container {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 99999;
  pointer-events: auto;
  display: block !important;
  width: auto;
  height: auto;
}

.floating-chat-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #4F46E5;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  display: flex !important;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  position: relative;
  transform: scale(1);
  opacity: 1;
  visibility: visible !important;
}

.floating-chat-button:hover {
  transform: translateY(-2px) scale(1.05);
  background: #4338CA;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.floating-chat-button:active {
  transform: translateY(0) scale(0.95);
}

.floating-chat-button.has-unread {
  animation: pulse 2s infinite;
}

.unread-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #EF4444;
  color: white;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  border: 2px solid white;
  z-index: 1;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(79, 70, 229, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
  }
}

@media (max-width: 768px) {
  .floating-chat-container {
    bottom: 1rem;
    right: 1rem;
  }
  
  .floating-chat-button {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }
}
</style> 