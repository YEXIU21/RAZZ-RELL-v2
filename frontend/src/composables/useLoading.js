import { ref } from 'vue';

const loadingStates = ref(new Map());
const globalLoading = ref(false);

export function useLoading() {
  const startLoading = (key = 'global') => {
    if (key === 'global') {
      globalLoading.value = true;
    } else {
      loadingStates.value.set(key, true);
    }
  };

  const stopLoading = (key = 'global') => {
    if (key === 'global') {
      globalLoading.value = false;
    } else {
      loadingStates.value.delete(key);
    }
  };

  const isLoading = (key = 'global') => {
    if (key === 'global') {
      return globalLoading.value;
    }
    return loadingStates.value.has(key);
  };

  const withLoading = async (key, callback) => {
    try {
      startLoading(key);
      return await callback();
    } finally {
      stopLoading(key);
    }
  };

  return {
    startLoading,
    stopLoading,
    isLoading,
    withLoading,
    globalLoading,
    loadingStates,
  };
} 