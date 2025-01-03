<template>
  <div class="emoji-picker" :style="position">
    <div class="emoji-header">
      <div class="search-container">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search emojis..."
          class="search-input"
        >
      </div>
      <div class="category-tabs">
        <button
          v-for="category in categories"
          :key="category.id"
          @click="selectCategory(category.id)"
          :class="['category-tab', { active: selectedCategory === category.id }]"
          :title="category.name"
        >
          <i :class="category.icon"></i>
        </button>
      </div>
    </div>

    <div class="emoji-content" ref="emojiContent">
      <template v-if="searchQuery">
        <div class="emoji-group">
          <div class="emoji-grid">
            <button
              v-for="emoji in filteredEmojis"
              :key="emoji.id"
              @click="selectEmoji(emoji)"
              class="emoji-btn"
              :title="emoji.name"
            >
              {{ emoji.char }}
            </button>
          </div>
        </div>
      </template>
      <template v-else>
        <div
          v-for="group in groupedEmojis"
          :key="group.category"
          class="emoji-group"
          :id="'category-' + group.category"
        >
          <h3 class="group-title">{{ getCategoryName(group.category) }}</h3>
          <div class="emoji-grid">
            <button
              v-for="emoji in group.emojis"
              :key="emoji.id"
              @click="selectEmoji(emoji)"
              class="emoji-btn"
              :title="emoji.name"
            >
              {{ emoji.char }}
            </button>
          </div>
        </div>
      </template>
    </div>

    <div class="emoji-footer">
      <div class="recent-emojis">
        <button
          v-for="emoji in recentEmojis"
          :key="emoji.id"
          @click="selectEmoji(emoji)"
          class="emoji-btn"
          :title="emoji.name"
        >
          {{ emoji.char }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import emojis from '@/data/emojis.json';

export default {
  name: 'ChatEmoji',
  props: {
    position: {
      type: Object,
      default: () => ({
        bottom: '60px',
        right: '0'
      })
    }
  },
  setup(props, { emit }) {
    const searchQuery = ref('');
    const selectedCategory = ref('smileys');
    const recentEmojis = ref([]);
    const emojiContent = ref(null);

    const categories = [
      { id: 'smileys', name: 'Smileys & Emotion', icon: 'fas fa-smile' },
      { id: 'people', name: 'People & Body', icon: 'fas fa-user' },
      { id: 'nature', name: 'Animals & Nature', icon: 'fas fa-leaf' },
      { id: 'food', name: 'Food & Drink', icon: 'fas fa-utensils' },
      { id: 'activities', name: 'Activities', icon: 'fas fa-futbol' },
      { id: 'travel', name: 'Travel & Places', icon: 'fas fa-plane' },
      { id: 'objects', name: 'Objects', icon: 'fas fa-lightbulb' },
      { id: 'symbols', name: 'Symbols', icon: 'fas fa-heart' },
      { id: 'flags', name: 'Flags', icon: 'fas fa-flag' }
    ];

    const groupedEmojis = computed(() => {
      return categories.map(category => ({
        category: category.id,
        emojis: emojis.filter(emoji => emoji.category === category.id)
      }));
    });

    const filteredEmojis = computed(() => {
      if (!searchQuery.value) return [];
      const query = searchQuery.value.toLowerCase();
      return emojis.filter(emoji => 
        emoji.name.toLowerCase().includes(query) ||
        emoji.keywords.some(keyword => keyword.toLowerCase().includes(query))
      );
    });

    const getCategoryName = (categoryId) => {
      return categories.find(c => c.id === categoryId)?.name || '';
    };

    const selectCategory = (categoryId) => {
      selectedCategory.value = categoryId;
      const element = document.getElementById(`category-${categoryId}`);
      if (element && emojiContent.value) {
        emojiContent.value.scrollTop = element.offsetTop;
      }
    };

    const selectEmoji = (emoji) => {
      emit('select', emoji);
      addToRecent(emoji);
    };

    const addToRecent = (emoji) => {
      const exists = recentEmojis.value.findIndex(e => e.id === emoji.id);
      if (exists !== -1) {
        recentEmojis.value.splice(exists, 1);
      }
      recentEmojis.value.unshift(emoji);
      if (recentEmojis.value.length > 8) {
        recentEmojis.value.pop();
      }
      localStorage.setItem('recentEmojis', JSON.stringify(recentEmojis.value));
    };

    const loadRecentEmojis = () => {
      try {
        const saved = localStorage.getItem('recentEmojis');
        if (saved) {
          recentEmojis.value = JSON.parse(saved);
        }
      } catch (error) {
        console.error('Failed to load recent emojis:', error);
      }
    };

    const handleClickOutside = (event) => {
      if (!event.target.closest('.emoji-picker')) {
        emit('close');
      }
    };

    onMounted(() => {
      loadRecentEmojis();
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      searchQuery,
      selectedCategory,
      categories,
      recentEmojis,
      emojiContent,
      groupedEmojis,
      filteredEmojis,
      getCategoryName,
      selectCategory,
      selectEmoji
    };
  }
};
</script>

<style scoped>
.emoji-picker {
  position: absolute;
  width: 320px;
  height: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  z-index: 1000;
}

.emoji-header {
  padding: 12px;
  border-bottom: 1px solid #E5E7EB;
}

.search-container {
  margin-bottom: 12px;
}

.search-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  font-size: 14px;
}

.category-tabs {
  display: flex;
  gap: 4px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.category-tabs::-webkit-scrollbar {
  height: 4px;
}

.category-tabs::-webkit-scrollbar-track {
  background: #F3F4F6;
  border-radius: 2px;
}

.category-tabs::-webkit-scrollbar-thumb {
  background: #D1D5DB;
  border-radius: 2px;
}

.category-tab {
  padding: 8px;
  background: none;
  border: none;
  border-radius: 4px;
  color: #6B7280;
  cursor: pointer;
  transition: all 0.2s;
}

.category-tab:hover {
  background: #F3F4F6;
  color: #374151;
}

.category-tab.active {
  background: #EEF2FF;
  color: #4F46E5;
}

.emoji-content {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
}

.emoji-content::-webkit-scrollbar {
  width: 4px;
}

.emoji-content::-webkit-scrollbar-track {
  background: #F3F4F6;
  border-radius: 2px;
}

.emoji-content::-webkit-scrollbar-thumb {
  background: #D1D5DB;
  border-radius: 2px;
}

.emoji-group {
  margin-bottom: 16px;
}

.group-title {
  font-size: 12px;
  color: #6B7280;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
}

.emoji-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  background: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.emoji-btn:hover {
  background: #F3F4F6;
}

.emoji-footer {
  padding: 12px;
  border-top: 1px solid #E5E7EB;
}

.recent-emojis {
  display: flex;
  gap: 4px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.recent-emojis::-webkit-scrollbar {
  height: 4px;
}

.recent-emojis::-webkit-scrollbar-track {
  background: #F3F4F6;
  border-radius: 2px;
}

.recent-emojis::-webkit-scrollbar-thumb {
  background: #D1D5DB;
  border-radius: 2px;
}

@media (max-width: 768px) {
  .emoji-picker {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 50vh;
    border-radius: 12px 12px 0 0;
  }
}
</style> 