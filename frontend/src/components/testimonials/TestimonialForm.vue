<template>
  <div class="testimonial-form">
    <h2>Share Your Experience</h2>
    <p class="form-description">
      Tell us about your event experience. Your feedback helps us improve and inspires others!
    </p>

    <form @submit.prevent="submitTestimonial" class="form-content">
      <!-- Rating -->
      <div class="form-group">
        <label>Rating</label>
        <div class="rating-input">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            class="star-btn"
            :class="{ active: star <= form.rating }"
            @click="form.rating = star"
          >
            <i class="fas fa-star"></i>
          </button>
        </div>
        <span class="error-message" v-if="v$.rating.$error">
          {{ v$.rating.$errors[0].$message }}
        </span>
      </div>

      <!-- Event Type -->
      <div class="form-group">
        <label>Event Type</label>
        <select 
          v-model="form.eventType"
          :class="{ 'has-error': v$.eventType.$error }"
        >
          <option value="">Select event type</option>
          <option value="wedding">Wedding</option>
          <option value="corporate">Corporate Event</option>
          <option value="birthday">Birthday Party</option>
          <option value="social">Social Gathering</option>
          <option value="other">Other</option>
        </select>
        <span class="error-message" v-if="v$.eventType.$error">
          {{ v$.eventType.$errors[0].$message }}
        </span>
      </div>

      <!-- Testimonial Content -->
      <div class="form-group">
        <label>Your Experience</label>
        <textarea
          v-model="form.content"
          :class="{ 'has-error': v$.content.$error }"
          placeholder="Share your experience with us..."
          rows="4"
        ></textarea>
        <span class="error-message" v-if="v$.content.$error">
          {{ v$.content.$errors[0].$message }}
        </span>
      </div>

      <!-- Image Upload -->
      <div class="form-group">
        <label>Event Photos (Optional)</label>
        <div 
          class="image-upload-zone"
          @drop.prevent="handleDrop"
          @dragover.prevent
          @click="$refs.fileInput.click()"
        >
          <input
            type="file"
            ref="fileInput"
            multiple
            accept="image/*"
            @change="handleFileSelect"
            class="hidden"
          />
          <div class="upload-prompt" v-if="!selectedImages.length">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Drop images here or click to upload</p>
            <span class="file-limit">Maximum 5 images, 5MB each</span>
          </div>
          <div class="selected-images" v-else>
            <div 
              v-for="(image, index) in selectedImages"
              :key="index"
              class="image-preview"
            >
              <img :src="image.preview" :alt="image.name" />
              <button 
                type="button"
                class="remove-image"
                @click.stop="removeImage(index)"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div 
              v-if="selectedImages.length < 5"
              class="add-more"
              @click.stop="$refs.fileInput.click()"
            >
              <i class="fas fa-plus"></i>
              <span>Add More</span>
            </div>
          </div>
        </div>
        <span class="error-message" v-if="imageError">
          {{ imageError }}
        </span>
      </div>

      <!-- Submit Button -->
      <button 
        type="submit"
        class="submit-btn"
        :disabled="isSubmitting || v$.$invalid"
      >
        <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
        <span v-else>Submit Testimonial</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, maxLength } from '@vuelidate/validators';
import { useTestimonials } from '@/composables/useTestimonials';

const { addTestimonial } = useTestimonials();

const form = reactive({
  rating: 0,
  eventType: '',
  content: '',
});

const rules = {
  rating: { required },
  eventType: { required },
  content: { required, minLength: minLength(10), maxLength: maxLength(500) }
};

const v$ = useVuelidate(rules, form);

const fileInput = ref(null);
const selectedImages = ref([]);
const imageError = ref('');
const isSubmitting = ref(false);

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  addImages(files);
};

const handleDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  addImages(files);
};

const addImages = (files) => {
  imageError.value = '';

  // Validate file count
  if (selectedImages.value.length + files.length > 5) {
    imageError.value = 'Maximum 5 images allowed';
    return;
  }

  // Process each file
  files.forEach(file => {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      imageError.value = 'Only image files are allowed';
      return;
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      imageError.value = 'Each image must be less than 5MB';
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      selectedImages.value.push({
        file,
        name: file.name,
        preview: e.target.result
      });
    };
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  selectedImages.value.splice(index, 1);
  imageError.value = '';
};

const submitTestimonial = async () => {
  const isValid = await v$.value.$validate();
  if (!isValid) return;

  isSubmitting.value = true;
  try {
    const formData = new FormData();
    formData.append('rating', form.rating);
    formData.append('eventType', form.eventType);
    formData.append('content', form.content);
    
    selectedImages.value.forEach(image => {
      formData.append('images', image.file);
    });

    await addTestimonial(formData);

    // Reset form
    form.rating = 0;
    form.eventType = '';
    form.content = '';
    selectedImages.value = [];
    v$.value.$reset();

  } catch (error) {
    console.error('Error submitting testimonial:', error);
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.testimonial-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

h2 {
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.form-description {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.form-content {
  display: grid;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  color: var(--text-color);
  font-weight: 500;
}

.rating-input {
  display: flex;
  gap: 0.5rem;
}

.star-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: color 0.2s;
}

.star-btn.active {
  color: var(--star-color, #ffd700);
}

select,
textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
}

select {
  cursor: pointer;
}

textarea {
  resize: vertical;
  min-height: 100px;
}

.has-error {
  border-color: var(--danger-color);
}

.error-message {
  color: var(--danger-color);
  font-size: 0.875rem;
}

.image-upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s;
}

.image-upload-zone:hover {
  border-color: var(--primary-color);
}

.upload-prompt {
  color: var(--text-muted);
}

.upload-prompt i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.file-limit {
  font-size: 0.875rem;
  opacity: 0.7;
}

.selected-images {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 1rem;
}

.image-preview {
  position: relative;
  aspect-ratio: 1;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.remove-image {
  position: absolute;
  top: -0.5rem;
  right: -0.5rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--danger-color);
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.remove-image:hover {
  background: var(--danger-hover);
}

.add-more {
  aspect-ratio: 1;
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
}

.add-more:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.submit-btn {
  padding: 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.hidden {
  display: none;
}

@media (max-width: 640px) {
  .testimonial-form {
    padding: 1rem;
  }

  .rating-input {
    justify-content: center;
  }

  .star-btn {
    padding: 0.75rem;
  }
}
</style> 