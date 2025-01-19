<template>
  <div class="loading-container">
    <div v-if="!showSuccess" class="loading-squares">
      <div v-for="i in 5" :key="i" class="loading-square" :style="{ animationDelay: `${i * 0.1}s` }"></div>
    </div>
    <div v-else class="success-message">
      <span class="checkmark">✓</span>
      Successfully Added!
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingAnimation',
  props: {
    showSuccess: Boolean
  }
}
</script>

<style scoped>
.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50px;
}

.loading-squares {
  display: flex;
}

.loading-square {
  width: 20px;
  height: 20px;
  background-color: #3498db;
  margin: 0 5px;
  border-radius: 3px;
  animation: jump 0.5s ease-in-out infinite;
}

@keyframes jump {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

.success-message {
  display: flex;
  align-items: center;
  font-size: 1.2rem;
  color: #4CAF50;
}

.checkmark {
  font-size: 1.5rem;
  margin-right: 0.5rem;
}
</style> 