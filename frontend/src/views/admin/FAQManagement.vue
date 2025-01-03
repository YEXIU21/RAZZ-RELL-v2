<template>
  <div class="faq-management">
    <div class="page-header">
      <h1>FAQ Management</h1>
      <div class="header-actions">
        <button 
          class="add-category-btn"
          @click="openCategoryModal"
        >
          <i class="fas fa-folder-plus"></i>
          Add Category
        </button>
        <button 
          class="add-faq-btn"
          @click="openFAQModal"
        >
          <i class="fas fa-plus"></i>
          Add FAQ
        </button>
      </div>
    </div>

    <!-- Category Management -->
    <div class="categories-section">
      <h2>Categories</h2>
      <div class="categories-grid">
        <div 
          v-for="category in categories"
          :key="category.id"
          class="category-card"
        >
          <div class="category-header">
            <div class="category-info">
              <i :class="category.icon"></i>
              <h3>{{ category.name }}</h3>
            </div>
            <div class="category-actions">
              <button 
                class="edit-btn"
                @click="editCategory(category)"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button 
                class="delete-btn"
                @click="confirmDeleteCategory(category)"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          <p class="faq-count">
            {{ getFAQCount(category.id) }} FAQs
          </p>
        </div>
      </div>
    </div>

    <!-- FAQ Management -->
    <div class="faqs-section">
      <div class="section-header">
        <h2>FAQs</h2>
        <div class="filter-controls">
          <select v-model="selectedCategory">
            <option value="">All Categories</option>
            <option 
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>

          <div class="search-box">
            <i class="fas fa-search"></i>
            <input 
              type="text"
              v-model="searchQuery"
              placeholder="Search FAQs..."
            />
          </div>
        </div>
      </div>

      <!-- FAQ List -->
      <div class="faq-list">
        <draggable
          v-model="filteredFAQs"
          handle=".drag-handle"
          @end="handleReorder"
          item-key="id"
        >
          <template #item="{ element: faq }">
            <div class="faq-item">
              <div class="drag-handle">
                <i class="fas fa-grip-vertical"></i>
              </div>
              
              <div class="faq-content">
                <h3>{{ faq.question }}</h3>
                <p>{{ truncateText(faq.answer, 150) }}</p>
                
                <div class="faq-meta">
                  <span class="category-badge">
                    {{ getCategoryName(faq.categoryId) }}
                  </span>
                  <span class="date">
                    Last updated: {{ formatDate(faq.updatedAt) }}
                  </span>
                </div>
              </div>

              <div class="faq-actions">
                <button 
                  class="edit-btn"
                  @click="editFAQ(faq)"
                >
                  <i class="fas fa-edit"></i>
                  Edit
                </button>
                <button 
                  class="delete-btn"
                  @click="confirmDeleteFAQ(faq)"
                >
                  <i class="fas fa-trash"></i>
                  Delete
                </button>
              </div>
            </div>
          </template>
        </draggable>
      </div>

      <!-- Empty State -->
      <div v-if="!filteredFAQs.length" class="empty-state">
        <i class="fas fa-question-circle"></i>
        <h3>No FAQs Found</h3>
        <p v-if="searchQuery || selectedCategory">
          Try adjusting your filters or search query
        </p>
        <p v-else>
          Get started by adding your first FAQ
        </p>
        <button 
          class="add-faq-btn"
          @click="openFAQModal"
        >
          <i class="fas fa-plus"></i>
          Add FAQ
        </button>
      </div>
    </div>

    <!-- Category Modal -->
    <modal
      v-if="showCategoryModal"
      @close="closeCategoryModal"
      :title="editingCategory ? 'Edit Category' : 'Add Category'"
    >
      <div class="category-form">
        <div class="form-group">
          <label>Category Name</label>
          <input 
            type="text"
            v-model="categoryForm.name"
            placeholder="Enter category name"
          />
        </div>

        <div class="form-group">
          <label>Icon</label>
          <div class="icon-selector">
            <button
              v-for="icon in availableIcons"
              :key="icon"
              class="icon-btn"
              :class="{ active: categoryForm.icon === icon }"
              @click="categoryForm.icon = icon"
            >
              <i :class="icon"></i>
            </button>
          </div>
        </div>

        <div class="modal-actions">
          <button 
            class="secondary-btn"
            @click="closeCategoryModal"
          >
            Cancel
          </button>
          <button 
            class="primary-btn"
            @click="saveCategory"
            :disabled="!categoryForm.name || !categoryForm.icon || isLoading"
          >
            <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
            <span v-else>{{ editingCategory ? 'Update' : 'Add' }} Category</span>
          </button>
        </div>
      </div>
    </modal>

    <!-- FAQ Modal -->
    <modal
      v-if="showFAQModal"
      @close="closeFAQModal"
      :title="editingFAQ ? 'Edit FAQ' : 'Add FAQ'"
    >
      <div class="faq-form">
        <div class="form-group">
          <label>Category</label>
          <select 
            v-model="faqForm.categoryId"
            :class="{ 'has-error': v$.categoryId.$error }"
          >
            <option value="">Select category</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <span class="error-message" v-if="v$.categoryId.$error">
            {{ v$.categoryId.$errors[0].$message }}
          </span>
        </div>

        <div class="form-group">
          <label>Question</label>
          <input 
            type="text"
            v-model="faqForm.question"
            :class="{ 'has-error': v$.question.$error }"
            placeholder="Enter question"
          />
          <span class="error-message" v-if="v$.question.$error">
            {{ v$.question.$errors[0].$message }}
          </span>
        </div>

        <div class="form-group">
          <label>Answer</label>
          <textarea
            v-model="faqForm.answer"
            :class="{ 'has-error': v$.answer.$error }"
            placeholder="Enter answer"
            rows="6"
          ></textarea>
          <span class="error-message" v-if="v$.answer.$error">
            {{ v$.answer.$errors[0].$message }}
          </span>
        </div>

        <div class="form-group">
          <label>Related Links</label>
          <div 
            v-for="(link, index) in faqForm.links"
            :key="index"
            class="link-item"
          >
            <div class="link-inputs">
              <input 
                type="text"
                v-model="link.text"
                placeholder="Link text"
              />
              <input 
                type="url"
                v-model="link.url"
                placeholder="URL"
              />
            </div>
            <button 
              class="remove-link"
              @click="removeLink(index)"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
          <button 
            class="add-link-btn"
            @click="addLink"
            type="button"
          >
            <i class="fas fa-plus"></i>
            Add Link
          </button>
        </div>

        <div class="modal-actions">
          <button 
            class="secondary-btn"
            @click="closeFAQModal"
          >
            Cancel
          </button>
          <button 
            class="primary-btn"
            @click="saveFAQ"
            :disabled="v$.$invalid || isLoading"
          >
            <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
            <span v-else>{{ editingFAQ ? 'Update' : 'Add' }} FAQ</span>
          </button>
        </div>
      </div>
    </modal>

    <!-- Delete Confirmation Modal -->
    <confirmation-modal
      v-if="showDeleteModal"
      @confirm="confirmDelete"
      @cancel="closeDeleteModal"
      :title="deleteType === 'category' ? 'Delete Category' : 'Delete FAQ'"
      :message="deleteMessage"
    />
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, url } from '@vuelidate/validators';
import { useFAQ } from '@/composables/useFAQ';
import Modal from '@/components/ui/Modal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import draggable from 'vuedraggable';

const {
  faqs,
  categories,
  isLoading,
  fetchFAQs,
  fetchCategories,
  addFAQ,
  updateFAQ,
  deleteFAQ,
  addCategory,
  updateCategory,
  deleteCategory,
  reorderFAQs
} = useFAQ();

// UI State
const searchQuery = ref('');
const selectedCategory = ref('');
const showCategoryModal = ref(false);
const showFAQModal = ref(false);
const showDeleteModal = ref(false);
const editingCategory = ref(null);
const editingFAQ = ref(null);
const deleteType = ref('');
const itemToDelete = ref(null);

// Form State
const categoryForm = reactive({
  name: '',
  icon: ''
});

const faqForm = reactive({
  categoryId: '',
  question: '',
  answer: '',
  links: []
});

// Validation Rules
const rules = {
  categoryId: { required },
  question: { required, minLength: minLength(10) },
  answer: { required, minLength: minLength(20) },
  links: {
    $each: {
      text: { required },
      url: { required, url }
    }
  }
};

const v$ = useVuelidate(rules, faqForm);

// Available Icons
const availableIcons = [
  'fas fa-question-circle',
  'fas fa-info-circle',
  'fas fa-book',
  'fas fa-calendar-alt',
  'fas fa-credit-card',
  'fas fa-box',
  'fas fa-truck',
  'fas fa-user',
  'fas fa-cog',
  'fas fa-shield-alt'
];

// Computed Properties
const filteredFAQs = computed(() => {
  let filtered = [...faqs.value];

  if (selectedCategory.value) {
    filtered = filtered.filter(faq => faq.categoryId === selectedCategory.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(faq => 
      faq.question.toLowerCase().includes(query) ||
      faq.answer.toLowerCase().includes(query)
    );
  }

  return filtered;
});

const deleteMessage = computed(() => {
  if (deleteType.value === 'category') {
    return `Are you sure you want to delete the category "${itemToDelete.value?.name}"? This will also delete all FAQs in this category.`;
  }
  return `Are you sure you want to delete the FAQ "${itemToDelete.value?.question}"?`;
});

// Methods
const getFAQCount = (categoryId) => {
  return faqs.value.filter(faq => faq.categoryId === categoryId).length;
};

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId);
  return category?.name || 'Uncategorized';
};

const truncateText = (text, length) => {
  if (text.length <= length) return text;
  return text.substring(0, length) + '...';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Category Modal
const openCategoryModal = (category = null) => {
  if (category) {
    editingCategory.value = category;
    categoryForm.name = category.name;
    categoryForm.icon = category.icon;
  } else {
    editingCategory.value = null;
    categoryForm.name = '';
    categoryForm.icon = '';
  }
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  editingCategory.value = null;
  categoryForm.name = '';
  categoryForm.icon = '';
};

const saveCategory = async () => {
  if (!categoryForm.name || !categoryForm.icon) return;

  try {
    if (editingCategory.value) {
      await updateCategory(editingCategory.value.id, categoryForm);
    } else {
      await addCategory(categoryForm);
    }
    closeCategoryModal();
    await fetchCategories();
  } catch (error) {
    console.error('Error saving category:', error);
  }
};

// FAQ Modal
const openFAQModal = (faq = null) => {
  if (faq) {
    editingFAQ.value = faq;
    faqForm.categoryId = faq.categoryId;
    faqForm.question = faq.question;
    faqForm.answer = faq.answer;
    faqForm.links = [...(faq.links || [])];
  } else {
    editingFAQ.value = null;
    faqForm.categoryId = selectedCategory.value || '';
    faqForm.question = '';
    faqForm.answer = '';
    faqForm.links = [];
  }
  showFAQModal.value = true;
};

const closeFAQModal = () => {
  showFAQModal.value = false;
  editingFAQ.value = null;
  v$.value.$reset();
  faqForm.categoryId = '';
  faqForm.question = '';
  faqForm.answer = '';
  faqForm.links = [];
};

const saveFAQ = async () => {
  const isValid = await v$.value.$validate();
  if (!isValid) return;

  try {
    if (editingFAQ.value) {
      await updateFAQ(editingFAQ.value.id, faqForm);
    } else {
      await addFAQ(faqForm);
    }
    closeFAQModal();
    await fetchFAQs();
  } catch (error) {
    console.error('Error saving FAQ:', error);
  }
};

// Link Management
const addLink = () => {
  faqForm.links.push({ text: '', url: '' });
};

const removeLink = (index) => {
  faqForm.links.splice(index, 1);
};

// Delete Confirmation
const confirmDeleteCategory = (category) => {
  deleteType.value = 'category';
  itemToDelete.value = category;
  showDeleteModal.value = true;
};

const confirmDeleteFAQ = (faq) => {
  deleteType.value = 'faq';
  itemToDelete.value = faq;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  try {
    if (deleteType.value === 'category') {
      await deleteCategory(itemToDelete.value.id);
      await fetchCategories();
    } else {
      await deleteFAQ(itemToDelete.value.id);
      await fetchFAQs();
    }
    closeDeleteModal();
  } catch (error) {
    console.error('Error deleting item:', error);
  }
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deleteType.value = '';
  itemToDelete.value = null;
};

// Reordering
const handleReorder = async ({ oldIndex, newIndex }) => {
  if (oldIndex === newIndex) return;

  const orderedIds = filteredFAQs.value.map(faq => faq.id);
  try {
    await reorderFAQs(orderedIds);
  } catch (error) {
    console.error('Error reordering FAQs:', error);
  }
};

// Initial Load
onMounted(async () => {
  await Promise.all([
    fetchCategories(),
    fetchFAQs()
  ]);
});
</script>

<style scoped>
.faq-management {
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

.header-actions {
  display: flex;
  gap: 1rem;
}

.add-category-btn,
.add-faq-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.add-category-btn {
  background: var(--secondary-color);
  color: white;
  border: none;
}

.add-category-btn:hover {
  background: var(--secondary-hover);
}

.add-faq-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.add-faq-btn:hover {
  background: var(--primary-hover);
}

/* Categories Section */
.categories-section {
  margin-bottom: 3rem;
}

.categories-section h2 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.category-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-info i {
  font-size: 1.5rem;
  color: var(--primary-color);
}

.category-info h3 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin: 0;
}

.category-actions {
  display: flex;
  gap: 0.5rem;
}

.category-actions button {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.edit-btn {
  background: none;
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
}

.edit-btn:hover {
  background: var(--primary-color);
  color: white;
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

.faq-count {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin: 0;
}

/* FAQs Section */
.faqs-section {
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  font-size: 1.5rem;
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

.search-box {
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
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  min-width: 250px;
}

/* FAQ List */
.faq-list {
  margin-bottom: 2rem;
}

.faq-item {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  margin-bottom: 1rem;
}

.drag-handle {
  cursor: move;
  color: var(--text-muted);
  padding: 0.5rem;
}

.faq-content {
  flex: 1;
}

.faq-content h3 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin: 0 0 0.5rem;
}

.faq-content p {
  color: var(--text-muted);
  margin: 0 0 1rem;
}

.faq-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-badge {
  padding: 0.25rem 0.75rem;
  background: var(--primary-color);
  color: white;
  border-radius: 20px;
  font-size: 0.875rem;
}

.date {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.faq-actions {
  display: flex;
  gap: 0.5rem;
}

.faq-actions button {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

/* Modal Styles */
.category-form,
.faq-form {
  display: grid;
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

.icon-selector {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
  gap: 0.5rem;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-btn:hover,
.icon-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.link-item {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

.link-inputs {
  flex: 1;
  display: grid;
  gap: 0.5rem;
}

.remove-link {
  padding: 0.75rem;
  background: none;
  border: 1px solid var(--danger-color);
  color: var(--danger-color);
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-link:hover {
  background: var(--danger-color);
  color: white;
}

.add-link-btn {
  padding: 0.75rem;
  background: none;
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.add-link-btn:hover {
  background: var(--primary-color);
  color: white;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.modal-actions button {
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
  color: white;
  border: none;
}

.primary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 0;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .faq-management {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions button {
    flex: 1;
  }

  .section-header {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-controls {
    width: 100%;
    flex-direction: column;
  }

  .search-box {
    width: 100%;
  }

  .search-box input {
    width: 100%;
    min-width: 0;
  }

  .faq-item {
    flex-direction: column;
  }

  .faq-actions {
    justify-content: flex-end;
  }

  .link-item {
    flex-direction: column;
  }

  .remove-link {
    align-self: flex-end;
  }
}
</style> 