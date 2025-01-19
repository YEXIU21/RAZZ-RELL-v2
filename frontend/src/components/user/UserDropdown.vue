<template>
  <div class="dropdown-menu">
    <div class="user-info">{{ username }}</div>
    <button @click="openSettings">Settings</button>
    <button @click="handleDarkMode">Dark Mode</button>
    <button @click="handleLogout">Logout</button>
  </div>

  <ProfileSettings 
    v-if="isSettingsOpen"
    :isOpen="isSettingsOpen"
    @close="closeSettings"
  />
</template>

<script setup>
import { ref } from 'vue';
import ProfileSettings from './ProfileSettings.vue';

const isSettingsOpen = ref(false);

const openSettings = () => {
  isSettingsOpen.value = true;
};

const closeSettings = () => {
  isSettingsOpen.value = false;
};
</script> 