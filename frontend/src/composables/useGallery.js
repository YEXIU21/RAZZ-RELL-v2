import { ref } from 'vue';
import { useApi } from './useApi';
import { useLoading } from './useLoading';
import { useNotifications } from './useNotifications';

export function useGallery() {
  const api = useApi();
  const { isLoading, withLoading } = useLoading();
  const { showNotification } = useNotifications();

  const galleryItems = ref([]);
  const totalItems = ref(0);
  const currentPage = ref(1);
  const itemsPerPage = 12;

  // const fetchGalleryItems = async (category = 'all', page = 1) => {
  //   try {
  //     const response = await withLoading('gallery', () =>
  //       api.get('/gallery', {
  //         params: {
  //           category,
  //           page,
  //           limit: itemsPerPage
  //         }
  //       })
  //     );

  //     if (page === 1) {
  //       galleryItems.value = response.data.items;
  //     } else {
  //       galleryItems.value = [...galleryItems.value, ...response.data.items];
  //     }

  //     totalItems.value = response.data.total;
  //     currentPage.value = page;
  //   } catch (error) {
  //     showNotification({
  //       type: 'error',
  //       message: 'Failed to load gallery items. Please try again later.'
  //     });
  //     console.error('Error fetching gallery items:', error);
  //   }
  // };

  const hasMoreItems = () => {
    return currentPage.value * itemsPerPage < totalItems.value;
  };

  const loadMore = async (category) => {
    if (hasMoreItems()) {
      await fetchGalleryItems(category, currentPage.value + 1);
    }
  };

  return {
    galleryItems,
    totalItems,
    currentPage,
    isLoading,
    fetchGalleryItems,
    hasMoreItems,
    loadMore
  };
} 