import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const user = ref(null);
const token = ref(localStorage.getItem('auth_token'));
const isAuthenticated = computed(() => !!token.value);
const isAdmin = computed(() => localStorage.getItem('user_role') === 'admin' || localStorage.getItem('user_role') === 'staff');

export function useAuth() {
  const router = useRouter();

  const login = async (credentials) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/login', credentials);
      
      if (response.data.status === 200) {
        token.value = response.data.token;
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_role', response.data.user.role);
        localStorage.setItem('user_info', JSON.stringify(response.data.user));
        user.value = response.data.user;

        const redirectPath = router.currentRoute.value.query.redirect || (isAdmin.value ? '/admin' : '/');
        await router.push(redirectPath);
        
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'You are now logged in.',
        });
      } else {
        await Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Invalid email or password.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
      } 

    } catch (error) {
      console.error('Login error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred during login.',
      });
      return false;
    }
  };

  const logout = async () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_info');
    localStorage.removeItem('user_role');
    localStorage.removeItem('user');
    
    await Swal.fire({
      icon: 'success',
      title: 'Logged Out!',
      text: 'You have been successfully logged out.',
      timer: 1500,
      showConfirmButton: false
    });
  };

  const fetchUser = async () => {
    if (!token.value) return;

    try {
      const response = await fetch('http://127.0.0.1:8000/api/my-info');

      if (!response.ok) {
        throw new Error('Failed to fetch user');
      }

      user.value = localStorage.getItem('user_info');
    } catch (error) {
      console.error('Error fetching user:', error);
      logout();
    }
  };

  const register = async (userData) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/register', userData);
      
      if (response.data.status === 200) {
        token.value = response.data.token;
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_role', response.data.user.role);
        localStorage.setItem('user_info', JSON.stringify(response.data.user));
        user.value = response.data.user;
        
        const redirectPath = router.currentRoute.value.query.redirect || (isAdmin.value ? '/admin' : '/');
        await router.push(redirectPath);
        return response;
      }

    } catch (error) {
      console.error('Registration error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: error.response?.data?.message || 'An error occurred during registration. Please try again.',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Okay'
      });
      return false;
    }
  };

  const contactUs = async (userData) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/contact-us', userData);
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Your message has been sent successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
      } else {
        await Swal.fire({
          icon: 'error',
          title: 'Success!',
          text: 'Your account has been created successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
      }

    } catch (error) {
      console.error('Registration error:', error);
      return false;
    }
  };

  const updateProfile = async (profileData) => {
    try {
      const response = await fetch('http://localhost:3000/api/auth/profile', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token.value}`,
        },
        body: JSON.stringify(profileData),
      });

      if (!response.ok) {
        throw new Error('Failed to update profile');
      }

      const updatedUser = await response.json();
      user.value = updatedUser;
      
      return true;
    } catch (error) {
      console.error('Profile update error:', error);
      return false;
    }
  };

  const changePassword = async (passwordData) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/reset-password', passwordData);
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Your password has been reset successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
        return true;
      }
      return false;
    } catch (error) {
      console.error('Password change error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'Failed to reset password. Please try again.',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Okay'
      });
      return false;
    }
  };

  const forgotPassword = async (email) => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/forgot-password', { email });
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Password reset instructions have been sent to your email.',
        });
        return true;
      }
    } catch (error) {
      console.error('Forgot password error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred while processing your request.',
      });
      return false;
    }
  };

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    register,
    updateProfile,
    changePassword,
    forgotPassword,
    contactUs,
    fetchUser,
  };
} 