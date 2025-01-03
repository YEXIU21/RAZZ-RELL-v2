<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add Packs</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="package-form">
        <div class="form-group">
          <label for="guestCount">Guest Count</label>
          <input
            type="number"
            id="guestCount"
            v-model="formData.guestCount"
            required
            placeholder="Enter guest count"
          />
        </div>

        <div class="form-group">
          <label for="price">Price of per guest (₱)</label>
          <input
            type="number"
            id="price"
            v-model="formData.price"
            required
            min="0"
            step="0.01"
            placeholder="Enter price"
          />
        </div>

        <div class="form-actions">
          <!-- <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button> -->
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Adding...' : 'Add Pack' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useGuest } from '@/composables/useGuest';
import Swal from 'sweetalert2';

const { addGuest } = useGuest();

const emit = defineEmits(['close', 'success']);

const formData = reactive({
  guestCount: 0,
  price: 0
});

const isSubmitting = ref(false);

const handleSubmit = async () => {
  try {
    
    const guestData = {
      ...formData
    };
    
    const result = await addGuest({
      guestCount: formData.guestCount,
      price: formData.price
    });

    // isSubmitting.value = true;

    // const formDataToSend = new FormData();
    // Object.keys(formData).forEach(key => {
    //   if (key === 'inclusions') {
    //     formDataToSend.append(key, JSON.stringify(formData[key].filter(i => i.trim())));
    //   } else {
    //     formDataToSend.append(key, formData[key]);
    //   }
    // });

    // const response = await fetch('http://localhost:3000/api/admin/packages', {
    //   method: 'POST',
    //   headers: {
    //     Authorization: `Bearer ${token.value}`
    //   },
    //   body: formDataToSend
    // });

    // if (response.ok) {
    //   emit('success');
    // } else {
    //   throw new Error('Failed to add package');
    // }
  } catch (error) {
    console.error('Error adding package:', error);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--modal-background, #fff);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-color);
  cursor: pointer;
  padding: 0.5rem;
}

.package-form {
  display: flex;
  flex-direction: column;
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

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background, #fff);
  color: var(--text-color);
}

textarea {
  resize: vertical;
}

.inclusions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.inclusion-item {
  display: flex;
  gap: 0.5rem;
}

.remove-btn {
  padding: 0.5rem;
  background: var(--danger-color, #dc3545);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.add-btn {
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.image-upload {
  position: relative;
  height: 200px;
  border: 2px dashed var(--border-color, #ddd);
  border-radius: 6px;
  overflow: hidden;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-muted, #666);
}

.upload-placeholder i {
  font-size: 2rem;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--secondary-color, #6c757d);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
  }
}
</style> 