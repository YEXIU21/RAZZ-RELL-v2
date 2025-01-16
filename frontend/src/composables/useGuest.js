import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export function useGuest() {
  const router = useRouter();
  const API_URL = import.meta.env.VITE_API_URL;

  const addGuest = async (credentials) => {
    try {
      const response = await axios.post(`${API_URL}/add-guest`, credentials);

      if(response.data.status === 200){
        Swal.fire({
          title: 'Success',
          text: 'Guest limitations added successfully',
          icon: 'success'
        }); 
      }else{
        throw new Error('Failed to get all guests');
      }

    } catch (error) {
      console.error('Adding guest error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred during guest addition.',
      });
      return false;
    }
  };

  const getAllGuests = async () => {
    try {
      const response = await axios.get(`${API_URL}/get-all-guest`);
      console.log(response.data);  
    } catch (error) {
      console.error('Adding guest error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred during guest addition.',
      });
      return false;
    }
  };

  return {
    addGuest,
    getAllGuests
  };
} 