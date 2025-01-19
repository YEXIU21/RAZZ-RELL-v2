<template>
  <section class="faq-section">
    <div class="section-header">
      <h2>Frequently Asked Questions</h2>
      <p>Find answers to common questions about our event planning services</p>
    </div>

    <!-- Category Tabs -->
    <div class="category-tabs">
      <button
        v-for="category in categories"
        :key="category.id"
        class="category-tab"
        :class="{ active: selectedCategory === category.id }"
        @click="selectCategory(category.id)"
      >
        <i :class="category.icon"></i>
        {{ category.name }}
      </button>
    </div>

    <!-- Search Box -->
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input 
        type="text"
        v-model="searchQuery"
        placeholder="Search FAQs..."
      />
    </div>

    <!-- FAQ Accordion -->
    <div class="faq-accordion">
      <div 
        v-for="faq in filteredFAQs"
        :key="faq.id"
        class="faq-item"
        :class="{ active: openFAQ === faq.id }"
      >
        <button 
          class="faq-question"
          @click="toggleFAQ(faq.id)"
        >
          <span>{{ faq.question }}</span>
          <i class="fas" :class="openFAQ === faq.id ? 'fa-minus' : 'fa-plus'"></i>
        </button>
        <div 
          class="faq-answer"
          :style="{ maxHeight: openFAQ === faq.id ? answerHeight + 'px' : '0' }"
        >
          <div class="answer-content" ref="answerRefs">
            <p>{{ faq.answer }}</p>
            <div v-if="faq.links?.length" class="related-links">
              <h4>Related Links:</h4>
              <ul>
                <li v-for="link in faq.links" :key="link.url">
                  <a :href="link.url" target="_blank" rel="noopener">
                    {{ link.text }}
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!filteredFAQs.length" class="empty-state">
      <i class="fas fa-search"></i>
      <h3>No FAQs Found</h3>
      <p v-if="searchQuery">
        No FAQs match your search query. Try different keywords or browse by category.
      </p>
      <p v-else>
        No FAQs available in this category.
      </p>
    </div>

    <!-- Contact Support -->
    <div class="support-section">
      <h3>Still have questions?</h3>
      <p>Can't find what you're looking for? We're here to help!</p>
      <router-link to="/contact" class="contact-btn">
        <i class="fas fa-envelope"></i>
        Contact Support
      </router-link>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useFAQ } from '@/composables/useFAQ';

const { faqs, categories, isLoading, fetchFAQs, fetchCategories } = useFAQ();

// UI State
const selectedCategory = ref('');
const searchQuery = ref('');
const openFAQ = ref(null);
const answerHeight = ref(0);
const answerRefs = ref([]);

// Computed Properties
const filteredFAQs = computed(() => {
  let filtered = [...faqs.value];

  // Apply category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(faq => faq.categoryId === selectedCategory.value);
  }

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(faq => 
      faq.question.toLowerCase().includes(query) ||
      faq.answer.toLowerCase().includes(query)
    );
  }

  return filtered;
});

// Methods
const selectCategory = (categoryId) => {
  selectedCategory.value = categoryId;
  openFAQ.value = null;
};

const toggleFAQ = (faqId) => {
  if (openFAQ.value === faqId) {
    openFAQ.value = null;
  } else {
    openFAQ.value = faqId;
    // Get height of answer content after next DOM update
    nextTick(() => {
      const activeAnswer = answerRefs.value.find(el => 
        el.parentElement.parentElement.classList.contains('active')
      );
      if (activeAnswer) {
        answerHeight.value = activeAnswer.offsetHeight;
      }
    });
  }
};

// Watch for search query changes
watch(searchQuery, () => {
  openFAQ.value = null;
});

// Initial load
onMounted(async () => {
  await Promise.all([
    fetchCategories(),
    fetchFAQs()
  ]);
});
</script>

<style scoped>
.faq-section {
  padding: 4rem 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-header h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.section-header p {
  font-size: 1.1rem;
  color: var(--text-muted);
}

/* Category Tabs */
.category-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.category-tab {
  padding: 0.75rem 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  white-space: nowrap;
}

.category-tab:hover,
.category-tab.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

/* Search Box */
.search-box {
  position: relative;
  margin-bottom: 2rem;
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
  padding: 1rem 1rem 1rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 1rem;
}

/* FAQ Accordion */
.faq-accordion {
  display: grid;
  gap: 1rem;
  margin-bottom: 3rem;
}

.faq-item {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;
}

.faq-item.active {
  border-color: var(--primary-color);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.faq-question {
  width: 100%;
  padding: 1.5rem;
  background: var(--card-background);
  border: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  color: var(--text-color);
  font-size: 1.1rem;
  font-weight: 500;
  text-align: left;
}

.faq-item.active .faq-question {
  color: var(--primary-color);
}

.faq-question i {
  font-size: 1rem;
  transition: transform 0.3s;
}

.faq-item.active .faq-question i {
  transform: rotate(180deg);
}

.faq-answer {
  overflow: hidden;
  transition: max-height 0.3s ease-in-out;
}

.answer-content {
  padding: 0 1.5rem 1.5rem;
  color: var(--text-color);
  line-height: 1.6;
}

.related-links {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.related-links h4 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.related-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.related-links a {
  color: var(--primary-color);
  text-decoration: none;
  display: inline-block;
  margin: 0.25rem 0;
}

.related-links a:hover {
  text-decoration: underline;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 0;
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

/* Support Section */
.support-section {
  text-align: center;
  padding: 3rem 0;
  border-top: 1px solid var(--border-color);
}

.support-section h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.support-section p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.contact-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: var(--primary-color);
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: background-color 0.2s;
}

.contact-btn:hover {
  background: var(--primary-hover);
}

@media (max-width: 768px) {
  .faq-section {
    padding: 2rem 1rem;
  }

  .section-header h2 {
    font-size: 2rem;
  }

  .category-tabs {
    flex-wrap: nowrap;
    justify-content: flex-start;
    padding-bottom: 1rem;
  }

  .category-tab {
    flex: 0 0 auto;
  }

  .faq-question {
    padding: 1rem;
    font-size: 1rem;
  }

  .answer-content {
    padding: 0 1rem 1rem;
  }
}
</style> 