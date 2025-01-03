import { ref } from 'vue';
import { useAuth } from './useAuth';
import { useNotifications } from './useNotifications';
import { useLoading } from './useLoading';

const API_BASE_URL = 'http://localhost:3000/api';

export function useApi() {
  const { token } = useAuth();
  const { error: showError } = useNotifications();
  const { withLoading } = useLoading();

  const handleResponse = async (response) => {
    const data = await response.json();

    if (!response.ok) {
      const errorMessage = data.message || 'An error occurred';
      showError(errorMessage);
      throw new Error(errorMessage);
    }

    return data;
  };

  const request = async (endpoint, options = {}) => {
    const url = `${API_BASE_URL}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
      ...options.headers,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      return await handleResponse(response);
    } catch (error) {
      showError(error.message);
      throw error;
    }
  };

  const get = (endpoint, options = {}) => {
    return request(endpoint, {
      method: 'GET',
      ...options,
    });
  };

  const post = (endpoint, data, options = {}) => {
    return request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
      ...options,
    });
  };

  const put = (endpoint, data, options = {}) => {
    return request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
      ...options,
    });
  };

  const del = (endpoint, options = {}) => {
    return request(endpoint, {
      method: 'DELETE',
      ...options,
    });
  };

  // Bookings API
  const bookings = {
    getAll: (params = {}) => withLoading('bookings', () => 
      get('/bookings', { params })
    ),
    
    getById: (id) => withLoading(`booking-${id}`, () => 
      get(`/bookings/${id}`)
    ),
    
    create: (data) => withLoading('create-booking', () => 
      post('/bookings', data)
    ),
    
    update: (id, data) => withLoading(`update-booking-${id}`, () => 
      put(`/bookings/${id}`, data)
    ),
    
    delete: (id) => withLoading(`delete-booking-${id}`, () => 
      del(`/bookings/${id}`)
    ),
    
    updateStatus: (id, status) => withLoading(`update-booking-status-${id}`, () => 
      put(`/bookings/${id}/status`, { status })
    ),
  };

  // Packages API
  const packages = {
    getAll: () => withLoading('packages', () => 
      get('/packages')
    ),
    
    getById: (id) => withLoading(`package-${id}`, () => 
      get(`/packages/${id}`)
    ),
    
    create: (data) => withLoading('create-package', () => 
      post('/packages', data)
    ),
    
    update: (id, data) => withLoading(`update-package-${id}`, () => 
      put(`/packages/${id}`, data)
    ),
    
    delete: (id) => withLoading(`delete-package-${id}`, () => 
      del(`/packages/${id}`)
    ),
  };

  // Users API
  const users = {
    getAll: () => withLoading('users', () => 
      get('/users')
    ),
    
    getById: (id) => withLoading(`user-${id}`, () => 
      get(`/users/${id}`)
    ),
    
    create: (data) => withLoading('create-user', () => 
      post('/users', data)
    ),
    
    update: (id, data) => withLoading(`update-user-${id}`, () => 
      put(`/users/${id}`, data)
    ),
    
    delete: (id) => withLoading(`delete-user-${id}`, () => 
      del(`/users/${id}`)
    ),
    
    updateRole: (id, role) => withLoading(`update-user-role-${id}`, () => 
      put(`/users/${id}/role`, { role })
    ),
  };

  // Settings API
  const settings = {
    get: () => withLoading('settings', () => 
      get('/settings')
    ),
    
    update: (data) => withLoading('update-settings', () => 
      put('/settings', data)
    ),
    
    testEmail: (config) => withLoading('test-email', () => 
      post('/settings/test-email', config)
    ),
  };

  // Reports API
  const reports = {
    getBookings: (params) => withLoading('bookings-report', () => 
      get('/reports/bookings', { params })
    ),
    
    getRevenue: (params) => withLoading('revenue-report', () => 
      get('/reports/revenue', { params })
    ),
    
    getPopularPackages: (params) => withLoading('popular-packages-report', () => 
      get('/reports/popular-packages', { params })
    ),
    
    getUserActivity: (params) => withLoading('user-activity-report', () => 
      get('/reports/user-activity', { params })
    ),
    
    export: (type, params) => withLoading('export-report', () => 
      get(`/reports/export/${type}`, { 
        ...params,
        responseType: 'blob',
      })
    ),
  };

  return {
    request,
    get,
    post,
    put,
    del,
    bookings,
    packages,
    users,
    settings,
    reports,
  };
} 