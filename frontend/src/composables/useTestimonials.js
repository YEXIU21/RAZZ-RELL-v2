import { ref } from 'vue';
import { useApi } from './useApi';
import { useNotifications } from './useNotifications';
import { useLoading } from './useLoading';

export function useTestimonials() {
  const api = useApi();
  const { showNotification } = useNotifications();
  const { isLoading, withLoading } = useLoading();

  const testimonials = ref([]);
  const totalTestimonials = ref(0);
  const currentPage = ref(1);
  const itemsPerPage = 6;

  const fetchTestimonials = async (page = 1, filter = {}) => {
    try {
      const response = await withLoading('testimonials', () =>
        api.get('/testimonials', {
          params: {
            page,
            limit: itemsPerPage,
            ...filter
          }
        })
      );

      if (page === 1) {
        testimonials.value = response.data.items;
      } else {
        testimonials.value = [...testimonials.value, ...response.data.items];
      }

      totalTestimonials.value = response.data.total;
      currentPage.value = page;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to load testimonials. Please try again later.'
      });
      console.error('Error fetching testimonials:', error);
    }
  };

  const addTestimonial = async (testimonialData) => {
    try {
      const response = await withLoading('add-testimonial', () =>
        api.post('/testimonials', testimonialData)
      );

      showNotification({
        type: 'success',
        message: 'Thank you for your testimonial!'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to submit testimonial. Please try again.'
      });
      throw error;
    }
  };

  const updateTestimonial = async (id, testimonialData) => {
    try {
      const response = await withLoading('update-testimonial', () =>
        api.put(`/testimonials/${id}`, testimonialData)
      );

      showNotification({
        type: 'success',
        message: 'Testimonial updated successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to update testimonial. Please try again.'
      });
      throw error;
    }
  };

  const deleteTestimonial = async (id) => {
    try {
      await withLoading('delete-testimonial', () =>
        api.delete(`/testimonials/${id}`)
      );

      showNotification({
        type: 'success',
        message: 'Testimonial deleted successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to delete testimonial. Please try again.'
      });
      throw error;
    }
  };

  const approveTestimonial = async (id) => {
    try {
      const response = await withLoading('approve-testimonial', () =>
        api.put(`/testimonials/${id}/approve`)
      );

      showNotification({
        type: 'success',
        message: 'Testimonial approved successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to approve testimonial. Please try again.'
      });
      throw error;
    }
  };

  const rejectTestimonial = async (id, reason) => {
    try {
      const response = await withLoading('reject-testimonial', () =>
        api.put(`/testimonials/${id}/reject`, { reason })
      );

      showNotification({
        type: 'success',
        message: 'Testimonial rejected successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to reject testimonial. Please try again.'
      });
      throw error;
    }
  };

  const hasMoreTestimonials = () => {
    return currentPage.value * itemsPerPage < totalTestimonials.value;
  };

  const loadMore = async (filter = {}) => {
    if (hasMoreTestimonials()) {
      await fetchTestimonials(currentPage.value + 1, filter);
    }
  };

  return {
    testimonials,
    totalTestimonials,
    currentPage,
    isLoading,
    fetchTestimonials,
    addTestimonial,
    updateTestimonial,
    deleteTestimonial,
    approveTestimonial,
    rejectTestimonial,
    hasMoreTestimonials,
    loadMore
  };
} 