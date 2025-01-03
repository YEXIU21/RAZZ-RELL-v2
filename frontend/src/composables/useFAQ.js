import { ref } from 'vue';
import { useApi } from './useApi';
import { useNotifications } from './useNotifications';
import { useLoading } from './useLoading';

export function useFAQ() {
  const api = useApi();
  const { showNotification } = useNotifications();
  const { isLoading, withLoading } = useLoading();

  const faqs = ref([]);
  const categories = ref([]);

  const fetchFAQs = async (category = '') => {
    try {
      const response = await withLoading('faqs', () =>
        api.get('/faqs', {
          params: { category }
        })
      );
      faqs.value = response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to load FAQs. Please try again later.'
      });
      console.error('Error fetching FAQs:', error);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await withLoading('faq-categories', () =>
        api.get('/faqs/categories')
      );
      categories.value = response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to load FAQ categories.'
      });
      console.error('Error fetching FAQ categories:', error);
    }
  };

  const addFAQ = async (faqData) => {
    try {
      const response = await withLoading('add-faq', () =>
        api.post('/faqs', faqData)
      );

      showNotification({
        type: 'success',
        message: 'FAQ added successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to add FAQ. Please try again.'
      });
      throw error;
    }
  };

  const updateFAQ = async (id, faqData) => {
    try {
      const response = await withLoading('update-faq', () =>
        api.put(`/faqs/${id}`, faqData)
      );

      showNotification({
        type: 'success',
        message: 'FAQ updated successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to update FAQ. Please try again.'
      });
      throw error;
    }
  };

  const deleteFAQ = async (id) => {
    try {
      await withLoading('delete-faq', () =>
        api.delete(`/faqs/${id}`)
      );

      showNotification({
        type: 'success',
        message: 'FAQ deleted successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to delete FAQ. Please try again.'
      });
      throw error;
    }
  };

  const addCategory = async (categoryData) => {
    try {
      const response = await withLoading('add-category', () =>
        api.post('/faqs/categories', categoryData)
      );

      showNotification({
        type: 'success',
        message: 'Category added successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to add category. Please try again.'
      });
      throw error;
    }
  };

  const updateCategory = async (id, categoryData) => {
    try {
      const response = await withLoading('update-category', () =>
        api.put(`/faqs/categories/${id}`, categoryData)
      );

      showNotification({
        type: 'success',
        message: 'Category updated successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to update category. Please try again.'
      });
      throw error;
    }
  };

  const deleteCategory = async (id) => {
    try {
      await withLoading('delete-category', () =>
        api.delete(`/faqs/categories/${id}`)
      );

      showNotification({
        type: 'success',
        message: 'Category deleted successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to delete category. Please try again.'
      });
      throw error;
    }
  };

  const reorderFAQs = async (orderedIds) => {
    try {
      await withLoading('reorder-faqs', () =>
        api.put('/faqs/reorder', { orderedIds })
      );

      showNotification({
        type: 'success',
        message: 'FAQs reordered successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to reorder FAQs. Please try again.'
      });
      throw error;
    }
  };

  return {
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
  };
} 