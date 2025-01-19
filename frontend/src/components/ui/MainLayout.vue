<template>
  <div class="main-layout">
    <header>
      <!-- Add your header content -->
    </header>
    
    <main>
      <slot></slot>
    </main>
    
    <footer>
      <!-- Add your footer content -->
    </footer>
  </div>
</template>

<script>
export default {
  name: 'MainLayout'
}
</script>

<style scoped>
.main-layout {
  /* Add your styles */
}
</style> 