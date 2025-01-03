<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Portfolio</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="portfolio-form">
        <div class="form-group">
          <label for="portfolioName">Portfolio Title</label>
          <input
            type="text"
            id="portfolioName"
            v-model="formData.title"
            required
            placeholder="Enter Portfolio Title"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="description">Portfolio Description</label>
          <textarea
            id="description"
            v-model="formData.description"
            required
            rows="4"
            placeholder="Enter Portfolio Description"
            class="form-input"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="eventType">Event Type</label>
          <select id="eventType" v-model="formData.eventType" required class="form-input" @change="handleEventTypeChange">
            <option value="">Select Event Type</option>
            <option value="Wedding">Wedding</option>
            <option value="Debut">Debut</option>
            <option value="Christening">Christening</option>
            <option value="Party">Party</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group" v-if="formData.eventType === 'Other'">
          <label for="customEventType">Custom Event Type</label>
          <input
            type="text"
            id="customEventType"
            v-model="formData.customEventType"
            required
            placeholder="Enter custom event type"
            class="form-input"
          />
        </div>
                
        <div class="form-group">
          <label for="coverImage">Cover Image</label>
          <div class="upload-container" :class="{ 'has-image': imagePreview }">
            <div v-if="!imagePreview" class="upload-area">
              <div class="upload-content">
                <input 
                  type="file" 
                  id="coverImage"
                  ref="coverImageInput"
                  @change="handleImageUpload" 
                  accept="image/*" 
                  class="file-input"
                />
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload cover image</span>
                <p class="upload-hint">Supports: JPG, PNG, WebP (Max 5MB)</p>
              </div>
            </div>
            <div v-else class="preview-container">
              <img :src="imagePreview" alt="Preview" class="image-preview" />
              <div class="preview-actions">
                <button type="button" @click="handleEditImage" class="action-btn" v-if="hasAppliedImage">
                  <i class="fas fa-crop"></i> Edit
                </button>
                <button type="button" @click="handleChangeImage" class="action-btn">
                  <i class="fas fa-image"></i> Change Image
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="albumImages">Album Images</label>
          <div class="upload-container album-container" :class="{ 'has-images': albumPreviews.length > 0 }">
            <div v-if="albumPreviews.length === 0" class="upload-area">
              <div class="upload-content">
                <input 
                  type="file" 
                  id="albumImages"
                  ref="albumImagesInput"
                  @change="handleAlbumImagesUpload" 
                  accept="image/*" 
                  multiple 
                  class="file-input"
                />
                <i class="fas fa-images"></i>
                <span>Click to upload album images</span>
                <p class="upload-hint">Select multiple images (Max 10 images)</p>
              </div>
            </div>
            <div class="album-grid">
              <div v-for="(preview, index) in albumPreviews" :key="index" class="album-item">
                <img :src="preview.url" alt="Album preview" />
                <button type="button" @click="removeAlbumImage(index)" class="remove-image-btn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="add-more-btn" @click="handleAddMoreImages">
                <input
                  type="file"
                  ref="albumInput"
                  multiple
                  accept="image/*"
                  @change="handleAlbumImagesChange"
                  style="display: none"
                />
                <div class="add-more-content">
                  <span class="plus-icon">+</span>
                  <span>Add More</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Image Cropper Modal -->
  <Teleport to="body">
    <div v-if="showCropper" class="cropper-modal-wrapper">
      <div class="cropper-modal">
        <div class="cropper-modal-content">
          <div class="cropper-modal-header">
            <h3>Crop Image</h3>
            <button @click="cancelCrop" class="close-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="cropper-modal-body">
            <VueCropper
              ref="cropper"
              :src="cropperImage"
              :aspect-ratio="16/9"
              :view-mode="2"
              :guides="true"
              :background="true"
              :rotatable="true"
              :zoomable="true"
              :movable="true"
              :responsive="true"
              :modal="true"
              :autoCropArea="1"
              :center="true"
              :highlight="true"
              :cropBoxMovable="true"
              :cropBoxResizable="true"
              :toggleDragModeOnDblclick="true"
            ></VueCropper>
          </div>
          <div class="cropper-modal-footer">
            <div class="cropper-controls">
              <button type="button" @click="rotateLeft" class="control-btn">
                <i class="fas fa-undo"></i> Rotate Left
              </button>
              <button type="button" @click="rotateRight" class="control-btn">
                <i class="fas fa-redo"></i> Rotate Right
              </button>
              <button type="button" @click="zoomIn" class="control-btn">
                <i class="fas fa-search-plus"></i> Zoom In
              </button>
              <button type="button" @click="zoomOut" class="control-btn">
                <i class="fas fa-search-minus"></i> Zoom Out
              </button>
              <button type="button" @click="resetCrop" class="control-btn">
                <i class="fas fa-sync"></i> Reset
              </button>
            </div>
            <div class="action-buttons">
              <button type="button" @click="cancelCrop" class="btn-secondary">
                Cancel
              </button>
              <button type="button" @click="applyCrop" class="btn-primary">
                Apply
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import Swal from 'sweetalert2';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
  portfolio: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update']);

const formData = reactive({
  title: props.portfolio.title || '',
  description: props.portfolio.description || '',
  eventType: props.portfolio.event_type || '',
  customEventType: '',
  image: null,
  albumImages: [],
  removedImages: []
});

const imagePreview = ref(null);
const albumPreviews = ref([]);
const isSubmitting = ref(false);
const showCropper = ref(false);
const cropperImage = ref(null);
const cropper = ref(null);
const hasAppliedImage = ref(false);
const albumInput = ref(null);
const removedImagePaths = ref(new Set());

const handleImageUpload = (event) => {
  const file = event.target?.files?.[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    Swal.fire({
      title: 'Error',
      text: 'Please upload a valid image file (JPEG, PNG, or WebP)',
      icon: 'error'
    });
    return;
  }

  formData.image = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    cropperImage.value = e.target.result;
    showCropper.value = true;
  };
  reader.readAsDataURL(file);
};

const handleAlbumImagesUpload = (event) => {
  const files = Array.from(event.target.files || []);
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  const maxSize = 5 * 1024 * 1024; // 5MB

  const validFiles = files.filter(file => {
    if (!allowedTypes.includes(file.type)) {
      return false;
    }
    if (file.size > maxSize) {
      alert(`File ${file.name} is too large. Maximum size is 5MB.`);
      return false;
    }
    return true;
  });

  if (validFiles.length !== files.length) {
    alert('Some files were skipped. Please ensure all files are images (JPEG, PNG, or WebP) and under 5MB.');
  }

  // Check total number of images
  if (formData.albumImages.length + validFiles.length > 10) {
    alert('Maximum 10 images allowed in the album.');
    return;
  }

  formData.albumImages.push(...validFiles);

  validFiles.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      albumPreviews.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const removeAlbumImage = (index) => {
  const removedPreview = albumPreviews.value[index];
  
  if (removedPreview.isExisting && removedPreview.path) {
    formData.removedImages.push(removedPreview.path);
    removedImagePaths.value.add(removedPreview.path);
  }
  
  // If it's a new image, remove it from albumImages array
  if (!removedPreview.isExisting) {
    const newImageIndex = formData.albumImages.findIndex(
      (_, i) => !albumPreviews.value.slice(0, index).find(p => !p.isExisting)
    );
    if (newImageIndex !== -1) {
      formData.albumImages.splice(newImageIndex, 1);
    }
  }
  
  albumPreviews.value.splice(index, 1);
};

const handleEventTypeChange = () => {
  if (formData.eventType !== 'Other') {
    formData.customEventType = '';
  }
};

const rotateLeft = () => {
  if (cropper.value) {
    cropper.value.rotate(-90);
  }
};

const rotateRight = () => {
  if (cropper.value) {
    cropper.value.rotate(90);
  }
};

const zoomIn = () => {
  if (cropper.value) {
    cropper.value.zoom(0.1);
  }
};

const zoomOut = () => {
  if (cropper.value) {
    cropper.value.zoom(-0.1);
  }
};

const resetCrop = () => {
  if (cropper.value) {
    cropper.value.reset();
  }
};

const cancelCrop = () => {
  showCropper.value = false;
  if (!imagePreview.value) {
    formData.image = null;
    cropperImage.value = null;
  }
};

const applyCrop = () => {
  if (!cropper.value) return;

  try {
    const canvas = cropper.value.getCroppedCanvas({
      width: 1600,
      height: 900,
      imageSmoothingQuality: 'high',
      fillColor: '#fff'
    });
    
    imagePreview.value = canvas.toDataURL('image/jpeg', 0.9);
    
    canvas.toBlob((blob) => {
      if (blob) {
        formData.image = new File([blob], 'portfolio-image.jpg', { 
          type: 'image/jpeg',
          lastModified: new Date().getTime()
        });
        hasAppliedImage.value = true;
      }
    }, 'image/jpeg', 0.9);
    
    showCropper.value = false;
  } catch (error) {
    console.error('Error applying crop:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to crop image. Please try again.',
      icon: 'error'
    });
  }
};

const handleSubmit = async () => {
  if (isSubmitting.value) return;
  
  try {
    isSubmitting.value = true;
    
    const formDataToSend = new FormData();
    formDataToSend.append('id', props.portfolio.id);
    formDataToSend.append('title', formData.title);
    formDataToSend.append('description', formData.description);
    formDataToSend.append('event_type', formData.eventType === 'Other' ? formData.customEventType : formData.eventType);
    
    if (formData.image) {
      formDataToSend.append('image', formData.image);
    }

    // Handle new album images
    if (formData.albumImages && formData.albumImages.length > 0) {
      formData.albumImages.forEach((image, index) => {
        formDataToSend.append(`album_images[${index}]`, image);
      });
    }

    // Send the list of images to remove
    if (formData.removedImages.length > 0) {
      formDataToSend.append('removed_images', JSON.stringify(Array.from(formData.removedImages)));
    }

    // Add existing images that weren't removed
    const remainingImages = albumPreviews.value
      .filter(preview => preview.isExisting && preview.path && !removedImagePaths.value.has(preview.path))
      .map(preview => preview.path);
    formDataToSend.append('existing_images', JSON.stringify(remainingImages));

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/update-portfolio`,
      formDataToSend,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );

    if (response.status === 200) {
      Swal.fire({
        title: 'Success',
        text: 'Portfolio updated successfully',
        icon: 'success',
      }).then(() => {
        emit('update');
        emit('close');
      });
    }
  } catch (error) {
    console.error('Error updating portfolio:', error);
    Swal.fire({
      title: 'Error',
      text: error.response?.data?.message || 'Failed to update portfolio. Please try again.',
      icon: 'error',
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handleEditImage = () => {
  if (imagePreview.value) {
    cropperImage.value = imagePreview.value;
    showCropper.value = true;
  }
};

const handleChangeImage = () => {
  hasAppliedImage.value = false;
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = handleImageUpload;
  input.click();
};

const handleAddMoreImages = () => {
  albumInput.value.click();
};

const handleAlbumImagesChange = (event) => {
  const files = Array.from(event.target.files);
  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      formData.albumImages.push(file);
      const reader = new FileReader();
      reader.onload = (e) => {
        albumPreviews.value.push({
          path: null,
          url: e.target.result,
          isExisting: false
        });
      };
      reader.readAsDataURL(file);
    }
  });
  event.target.value = '';
};

onMounted(() => {
  // Set initial event type
  if (props.portfolio.event_type) {
    const standardEventTypes = ['Wedding', 'Debut', 'Christening', 'Party'];
    if (standardEventTypes.includes(props.portfolio.event_type)) {
      formData.eventType = props.portfolio.event_type;
    } else {
      formData.eventType = 'Other';
      formData.customEventType = props.portfolio.event_type;
    }
  }

  // Set initial cover image
  if (props.portfolio.main_image_url) {
    const fullImageUrl = getImageUrl(props.portfolio.main_image_url);
    imagePreview.value = fullImageUrl;
  }

  // Set initial album images
  if (props.portfolio.images_url) {
    let images = [];
    try {
      images = typeof props.portfolio.images_url === 'string' 
        ? JSON.parse(props.portfolio.images_url) 
        : props.portfolio.images_url;
      
      // Store original image paths and create preview objects
      albumPreviews.value = images.map(img => ({
        path: img,
        url: getImageUrl(img),
        isExisting: true
      }));
    } catch (error) {
      console.error('Error parsing images:', error);
      albumPreviews.value = [];
    }
  }
});

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `${import.meta.env.VITE_API_URL}/storage/${path}`;
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

.portfolio-form {
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

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  background: white;
  color: #333;
  cursor: pointer;
}

.form-select:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.1);
}

.album-upload {
  margin-top: 1rem;
}

.album-previews {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.album-preview-item {
  position: relative;
  aspect-ratio: 1;
}

.album-preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
}

.album-preview-item .remove-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.album-preview-item .remove-btn:hover {
  background: rgba(255, 0, 0, 0.9);
}

.upload-container {
  position: relative;
  min-height: 200px;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.upload-area {
  position: relative;
  height: 200px;
  width: 100%;
  cursor: pointer;
}

.upload-content {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #6b7280;
  pointer-events: none;
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

.preview-container {
  position: relative;
  width: 100%;
  height: 200px;
  border-radius: 8px;
  overflow: hidden;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-actions {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.5rem;
  z-index: 5;
}

.action-btn {
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-weight: 500;
  color: #374151;
  backdrop-filter: blur(4px);
}

.action-btn:hover {
  background: white;
  transform: scale(1.05);
}

.action-btn i {
  font-size: 0.875rem;
}

.upload-hint {
  font-size: 0.875rem;
  color: #9ca3af;
}

/* Album grid styles */
.album-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
  padding: 1rem;
  border: 2px dashed #e2e8f0;
  border-radius: 0.5rem;
}

.album-item {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.album-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #ef4444;
  transition: all 0.2s;
}

.remove-image-btn:hover {
  background: #ef4444;
  color: white;
}

.add-more-btn {
  aspect-ratio: 1;
  border: 2px dashed #e2e8f0;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.add-more-btn:hover {
  border-color: #3b82f6;
  background: #f8fafc;
}

.add-more-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
}

.plus-icon {
  font-size: 1.5rem;
  font-weight: bold;
}

.add-more-btn:hover .add-more-content {
  color: #3b82f6;
}

.cropper-modal-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-modal {
  position: relative;
  width: 90%;
  max-width: 1200px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1),
              0 8px 32px rgba(0, 0, 0, 0.4);
}

.cropper-modal-content {
  display: flex;
  flex-direction: column;
  height: 90vh;
  max-height: 800px;
  background: #ffffff;
  border-radius: 12px;
  overflow: hidden;
}

.cropper-modal-header {
  padding: 1.25rem;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cropper-modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
  font-weight: 600;
}

.cropper-modal-body {
  flex: 1;
  background: #000000;
  position: relative;
  min-height: 400px;
  max-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-modal-body :deep(.vue-cropper) {
  width: 100%;
  height: 100%;
  background: #000000;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.cropper-modal-footer {
  padding: 1.25rem;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
}

/* Override vue-cropper default styles */
:deep(.cropper-view-box) {
  outline: 2px solid #3b82f6;
  outline-color: #3b82f6;
}

:deep(.cropper-line) {
  background-color: #3b82f6;
}

:deep(.cropper-point) {
  background-color: #3b82f6;
  width: 8px;
  height: 8px;
}

:deep(.cropper-point.point-se) {
  width: 10px;
  height: 10px;
}

:deep(.cropper-center) {
  width: 8px;
  height: 8px;
  background-color: #3b82f6;
}

:deep(.cropper-face) {
  background-color: inherit;
}

:deep(.cropper-dashed) {
  border-color: #3b82f6;
}

:deep(.cropper-modal) {
  opacity: 0.8;
}

.cropper-controls {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  justify-content: center;
}

.control-btn {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  white-space: nowrap;
}

.control-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  color: #374151;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.close-btn {
  background: transparent;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #374151;
}
</style> 