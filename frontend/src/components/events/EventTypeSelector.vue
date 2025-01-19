<template>
  <div class="event-type-selector">
    <h3>Select Event Type</h3>
    <div class="event-types">
      <div
        v-for="type in eventTypes"
        :key="type.id"
        class="event-type-card"
        :class="{ active: selectedType === type.id }"
        @click="selectEventType(type.id)"
      >
        <div class="icon">
          <i :class="type.icon"></i>
        </div>
        <h4>{{ type.name }}</h4>
        <p>{{ type.description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const selectedType = ref('');

const eventTypes = [
  {
    id: 'wedding',
    name: 'Wedding',
    icon: 'fas fa-rings',
    description: 'Celebrate your special day'
  },
  {
    id: 'debut',
    name: 'Debut',
    icon: 'fas fa-star',
    description: 'Mark your 18th birthday'
  },
  {
    id: 'christening',
    name: 'Christening',
    icon: 'fas fa-baby',
    description: 'Welcome your little one'
  },
  {
    id: 'party',
    name: 'Party',
    icon: 'fas fa-birthday-cake',
    description: 'Fun celebration for any occasion'
  },
  {
    id: 'other',
    name: 'Other',
    icon: 'fas fa-calendar-alt',
    description: 'Custom event celebrations'
  }
];

const emit = defineEmits(['select']);

const selectEventType = (type) => {
  selectedType.value = type;
  emit('select', type);
};
</script> 