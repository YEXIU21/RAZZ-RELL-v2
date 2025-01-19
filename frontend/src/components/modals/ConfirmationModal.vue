<template>
  <div class="confirmation-modal">
    <div class="modal-content">
      <h2>{{ title }}</h2>
      <p>{{ message }}</p>
      <div class="button-group">
        <button @click="$emit('confirm')" class="confirm-btn">Confirm</button>
        <button @click="$emit('cancel')" class="cancel-btn">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmationModal',
  props: {
    title: {
      type: String,
      default: 'Confirm Action'
    },
    message: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
</style> 