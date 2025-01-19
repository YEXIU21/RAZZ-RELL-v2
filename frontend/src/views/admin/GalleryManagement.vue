<template>
  <div class="gallery-management">
    <div class="page-header">
      <h1>Gallery Management</h1>
      <button class="primary-btn" @click="openUploadModal">
        <i class="fas fa-plus"></i>
        Add New Items
      </button>
    </div>

    <!-- Filter Controls -->
    <div class="filter-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search gallery items..."
        />
      </div>

      <div class="filter-controls">
        <select v-model="selectedCategory">
          <option value="all">All Categories</option>
          <option value="wedding">Weddings</option>
          <option value="corporate">Corporate</option>
          <option value="social">Social</option>
          <option value="themed">Themed</option>
        </select>

        <select v-model="sortBy">
          <option value="date-desc">Newest First</option>
          <option value="date-asc">Oldest First</option>
          <option value="name-asc">Name A-Z</option>
          <option value="name-desc">Name Z-A</option>
        </select>
      </div>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
      <div 
        v-for="item in filteredItems" 
        :key="item.id" 
        class="gallery-item"
      >
        <div class="item-preview">
          <img 
            :src="item.thumbnail" 
            :alt="item.title"
            loading="lazy"
          />
          <div class="item-type-badge" :class="item.type">
            <i :class="item.type === 'video' ? 'fas fa-video' : 'fas fa-image'"></i>
            {{ item.type }}
          </div>
        </div>

        <div class="item-details">
          <h3>{{ item.title }}</h3>
          <p class="category">{{ item.category }}</p>
          <p class="date">{{ formatDate(item.date) }}</p>
        </div>

        <div class="item-actions">
          <button class="edit-btn" @click="editItem(item)">
            <i class="fas fa-edit"></i>
            Edit
          </button>
          <button class="delete-btn" @click="confirmDelete(item)">
            <i class="fas fa-trash"></i>
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Load More -->
    <div class="load-more" v-if="hasMoreItems">
      <button 
        class="load-more-btn"
        @click="loadMore"
        :disabled="isLoading"
      >
        <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
        <span v-else>Load More</span>
      </button>
    </div>

    <!-- Upload Modal -->
    <modal
      v-if="showUploadModal"
      @close="closeUploadModal"
      title="Add Gallery Items"
    >
      <div class="upload-modal">
        <div class="upload-zone" @drop.prevent="handleDrop" @dragover.prevent>
          <input
            type="file"
            ref="fileInput"
            multiple
            accept="image/*,video/*"
            @change="handleFileSelect"
            class="hidden"
          />
          <div class="upload-prompt" v-if="!selectedFiles.length">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Drag and drop files here or</p>
            <button @click="$refs.fileInput.click()">Browse Files</button>
          </div>
          <div class="selected-files" v-else>
            <div 
              v-for="(file, index) in selectedFiles" 
              :key="index"
              class="file-item"
            >
              <img 
                v-if="file.type.startsWith('image')"
                :src="file.preview"
                alt="Preview"
              />
              <video 
                v-else-if="file.type.startsWith('video')"
                :src="file.preview"
                controls
              ></video>
              <div class="file-info">
                <span class="file-name">{{ file.name }}</span>
                <button @click="removeFile(index)">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="upload-form">
          <div class="form-group">
            <label>Category</label>
            <select v-model="uploadForm.category">
              <option value="wedding">Wedding</option>
              <option value="corporate">Corporate</option>
              <option value="social">Social</option>
              <option value="themed">Themed</option>
            </select>
          </div>

          <div class="form-group">
            <label>Title Pattern</label>
            <input 
              type="text"
              v-model="uploadForm.titlePattern"
              placeholder="e.g., Wedding {n} - will generate Wedding 1, Wedding 2, etc."
            />
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea
              v-model="uploadForm.description"
              placeholder="Enter description for the gallery items"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Location</label>
            <input
              type="text"
              v-model="uploadForm.location"
              placeholder="Enter event location"
            />
          </div>

          <div class="form-group">
            <label>Date</label>
            <input
              type="date"
              v-model="uploadForm.date"
            />
          </div>
        </div>

        <div class="modal-footer">
          <button 
            class="secondary-btn" 
            @click="closeUploadModal"
          >
            Cancel
          </button>
          <button 
            class="primary-btn" 
            @click="uploadFiles"
            :disabled="isUploading || !selectedFiles.length"
          >
            <i class="fas fa-spinner fa-spin" v-if="isUploading"></i>
            <span v-else>Upload Files</span>
          </button>
        </div>
      </div>
    </modal>

    <!-- Edit Modal -->
    <modal
      v-if="showEditModal"
      @close="closeEditModal"
      title="Edit Gallery Item"
    >
      <div class="edit-modal">
        <div class="preview">
          <img 
            v-if="editingItem?.type === 'image'"
            :src="editingItem?.url"
            :alt="editingItem?.title"
          />
          <video
            v-else-if="editingItem?.type === 'video'"
            :src="editingItem?.url"
            controls
          ></video>
        </div>

        <div class="edit-form">
          <div class="form-group">
            <label>Title</label>
            <input 
              type="text"
              v-model="editForm.title"
            />
          </div>

          <div class="form-group">
            <label>Category</label>
            <select v-model="editForm.category">
              <option value="wedding">Wedding</option>
              <option value="corporate">Corporate</option>
              <option value="social">Social</option>
              <option value="themed">Themed</option>
            </select>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea
              v-model="editForm.description"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Location</label>
            <input
              type="text"
              v-model="editForm.location"
            />
          </div>

          <div class="form-group">
            <label>Date</label>
            <input
              type="date"
              v-model="editForm.date"
            />
          </div>
        </div>

        <div class="modal-footer">
          <button 
            class="secondary-btn" 
            @click="closeEditModal"
          >
            Cancel
          </button>
          <button 
            class="primary-btn" 
            @click="saveEdit"
            :disabled="isSaving"
          >
            <i class="fas fa-spinner fa-spin" v-if="isSaving"></i>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </div>
    </modal>

    <!-- Delete Confirmation Modal -->
    <confirmation-modal
      v-if="showDeleteModal"
      @confirm="deleteItem"
      @cancel="closeDeleteModal"
      title="Delete Gallery Item"
      :message="'Are you sure you want to delete ' + itemToDelete?.title + '? This action cannot be undone.'"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useGallery } from '@/composables/useGallery';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmation } from '@/composables/useConfirmation';
import Modal from '@/components/ui/Modal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';

const { 
  galleryItems, 
  isLoading, 
  fetchGalleryItems, 
  hasMoreItems, 
  loadMore 
} = useGallery();

const { showNotification } = useNotifications();

// Search and filter state
const searchQuery = ref('');
const selectedCategory = ref('all');
const sortBy = ref('date-desc');

// Modal state
const showUploadModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const isUploading = ref(false);
const isSaving = ref(false);

// File upload state
const fileInput = ref(null);
const selectedFiles = ref([]);
const uploadForm = ref({
  category: 'wedding',
  titlePattern: '',
  description: '',
  location: '',
  date: new Date().toISOString().split('T')[0]
});

// Edit state
const editingItem = ref(null);
const itemToDelete = ref(null);
const editForm = ref({
  title: '',
  category: '',
  description: '',
  location: '',
  date: ''
});

// Computed properties
const filteredItems = computed(() => {
  let items = [...galleryItems.value];

  // Apply category filter
  if (selectedCategory.value !== 'all') {
    items = items.filter(item => item.category === selectedCategory.value);
  }

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    items = items.filter(item => 
      item.title.toLowerCase().includes(query) ||
      item.description.toLowerCase().includes(query) ||
      item.location.toLowerCase().includes(query)
    );
  }

  // Apply sorting
  switch (sortBy.value) {
    case 'date-desc':
      items.sort((a, b) => new Date(b.date) - new Date(a.date));
      break;
    case 'date-asc':
      items.sort((a, b) => new Date(a.date) - new Date(b.date));
      break;
    case 'name-asc':
      items.sort((a, b) => a.title.localeCompare(b.title));
      break;
    case 'name-desc':
      items.sort((a, b) => b.title.localeCompare(a.title));
      break;
  }

  return items;
});

// Watch for filter changes
watch([selectedCategory, sortBy], () => {
  fetchGalleryItems(selectedCategory.value);
});

// File handling methods
const handleDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  addFiles(files);
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  addFiles(files);
};

const addFiles = (files) => {
  const validFiles = files.filter(file => 
    file.type.startsWith('image/') || file.type.startsWith('video/')
  );

  validFiles.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      selectedFiles.value.push({
        file,
        name: file.name,
        type: file.type,
        preview: e.target.result
      });
    };
    reader.readAsDataURL(file);
  });
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

// Modal methods
const openUploadModal = () => {
  showUploadModal.value = true;
};

const closeUploadModal = () => {
  showUploadModal.value = false;
  selectedFiles.value = [];
  uploadForm.value = {
    category: 'wedding',
    titlePattern: '',
    description: '',
    location: '',
    date: new Date().toISOString().split('T')[0]
  };
};

const editItem = (item) => {
  editingItem.value = item;
  editForm.value = {
    title: item.title,
    category: item.category,
    description: item.description,
    location: item.location,
    date: item.date
  };
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  editingItem.value = null;
  editForm.value = {
    title: '',
    category: '',
    description: '',
    location: '',
    date: ''
  };
};

const confirmDelete = (item) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  itemToDelete.value = null;
};

// API methods
const uploadFiles = async () => {
  if (!selectedFiles.value.length) return;

  isUploading.value = true;
  try {
    const formData = new FormData();
    selectedFiles.value.forEach((file, index) => {
      formData.append('files', file.file);
    });
    formData.append('data', JSON.stringify(uploadForm.value));

    await api.post('/gallery/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    showNotification({
      type: 'success',
      message: 'Files uploaded successfully'
    });

    closeUploadModal();
    fetchGalleryItems(selectedCategory.value);
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to upload files. Please try again.'
    });
  } finally {
    isUploading.value = false;
  }
};

const saveEdit = async () => {
  if (!editingItem.value) return;

  isSaving.value = true;
  try {
    await api.put(`/gallery/${editingItem.value.id}`, editForm.value);

    showNotification({
      type: 'success',
      message: 'Gallery item updated successfully'
    });

    closeEditModal();
    fetchGalleryItems(selectedCategory.value);
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to update gallery item. Please try again.'
    });
  } finally {
    isSaving.value = false;
  }
};

const deleteItem = async () => {
  if (!itemToDelete.value) return;

  try {
    await api.delete(`/gallery/${itemToDelete.value.id}`);

    showNotification({
      type: 'success',
      message: 'Gallery item deleted successfully'
    });

    closeDeleteModal();
    fetchGalleryItems(selectedCategory.value);
  } catch (error) {
    showNotification({
      type: 'error',
      message: 'Failed to delete gallery item. Please try again.'
    });
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Initial load
fetchGalleryItems();
</script>

<style scoped>
.gallery-management {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  color: var(--text-color);
}

.filter-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 300px;
  position: relative;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-box input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
}

.filter-controls {
  display: flex;
  gap: 1rem;
}

.filter-controls select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.gallery-item {
  background: var(--card-background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.item-preview {
  position: relative;
  aspect-ratio: 16/9;
}

.item-preview img,
.item-preview video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-type-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  color: white;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.item-type-badge.image {
  background: var(--primary-color);
}

.item-type-badge.video {
  background: var(--accent-color);
}

.item-details {
  padding: 1rem;
}

.item-details h3 {
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.item-details .category {
  color: var(--primary-color);
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.item-details .date {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.item-actions {
  display: flex;
  padding: 1rem;
  gap: 0.5rem;
  border-top: 1px solid var(--border-color);
}

.item-actions button {
  flex: 1;
  padding: 0.5rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.edit-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.edit-btn:hover {
  background: var(--primary-hover);
}

.delete-btn {
  background: none;
  border: 1px solid var(--danger-color);
  color: var(--danger-color);
}

.delete-btn:hover {
  background: var(--danger-color);
  color: white;
}

/* Upload Modal Styles */
.upload-zone {
  border: 2px dashed var(--border-color);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  margin-bottom: 1.5rem;
}

.upload-prompt {
  color: var(--text-muted);
}

.upload-prompt i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.upload-prompt button {
  color: var(--primary-color);
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
}

.selected-files {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.file-item {
  position: relative;
}

.file-item img,
.file-item video {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border-radius: 8px;
}

.file-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  padding: 0.5rem;
  color: white;
  font-size: 0.875rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
}

.file-info button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}

.upload-form {
  display: grid;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: var(--text-color);
  font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

/* Edit Modal Styles */
.edit-modal {
  display: grid;
  gap: 1.5rem;
}

.preview {
  aspect-ratio: 16/9;
  border-radius: 12px;
  overflow: hidden;
}

.preview img,
.preview video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.modal-footer button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.secondary-btn {
  background: none;
  border: 1px solid var(--border-color);
  color: var(--text-color);
}

.primary-btn {
  background: var(--primary-color);
  border: none;
  color: white;
}

.primary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .gallery-management {
    padding: 1rem;
  }

  .filter-section {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-controls {
    width: 100%;
  }

  .filter-controls select {
    flex: 1;
  }
}
</style> 