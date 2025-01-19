import { ref } from 'vue';
import { useApi } from './useApi';
import { useNotifications } from './useNotifications';
import { useLoading } from './useLoading';

export function useSettings() {
  const api = useApi();
  const { showNotification } = useNotifications();
  const { isLoading, withLoading } = useLoading();

  const settings = ref({
    general: {
      siteName: '',
      siteDescription: '',
      contactEmail: '',
      contactPhone: '',
      address: '',
      businessHours: '',
      socialMedia: {
        facebook: '',
        instagram: '',
        twitter: '',
        linkedin: ''
      }
    },
    booking: {
      minAdvanceBookingDays: 7,
      maxAdvanceBookingDays: 180,
      depositPercentage: 30,
      cancellationPeriodDays: 14,
      refundPercentage: 80,
      allowInstallments: true,
      maxInstallments: 3,
      requirePhoneVerification: true,
      requireEmailVerification: true
    },
    notifications: {
      emailNotifications: true,
      smsNotifications: true,
      adminEmailNotifications: true,
      bookingConfirmation: true,
      bookingReminder: true,
      paymentReminder: true,
      eventReminder: true,
      feedbackRequest: true,
      marketingEmails: true
    },
    appearance: {
      theme: 'light',
      primaryColor: '#4f46e5',
      secondaryColor: '#3b82f6',
      accentColor: '#10b981',
      dangerColor: '#ef4444',
      warningColor: '#f59e0b',
      successColor: '#22c55e',
      fontFamily: 'Inter, sans-serif',
      borderRadius: '8px',
      enableAnimations: true
    },
    security: {
      sessionTimeout: 60,
      maxLoginAttempts: 5,
      lockoutDuration: 30,
      passwordMinLength: 8,
      requireSpecialChars: true,
      require2FA: false,
      allowRememberMe: true,
      allowSocialLogin: true
    },
    integrations: {
      paymentGateways: {
        stripe: {
          enabled: true,
          testMode: true,
          publicKey: '',
          secretKey: ''
        },
        paypal: {
          enabled: true,
          testMode: true,
          clientId: '',
          clientSecret: ''
        }
      },
      emailService: {
        provider: 'smtp',
        host: '',
        port: '',
        username: '',
        password: '',
        encryption: 'tls'
      },
      smsService: {
        provider: 'twilio',
        accountSid: '',
        authToken: '',
        fromNumber: ''
      },
      googleMaps: {
        enabled: true,
        apiKey: ''
      },
      analytics: {
        googleAnalytics: {
          enabled: true,
          trackingId: ''
        },
        facebookPixel: {
          enabled: true,
          pixelId: ''
        }
      }
    },
    maintenance: {
      maintenanceMode: false,
      maintenanceMessage: '',
      allowedIPs: [],
      scheduledMaintenance: null,
      backupEnabled: true,
      backupFrequency: 'daily',
      backupRetentionDays: 30,
      logRetentionDays: 90
    }
  });

  const fetchSettings = async () => {
    try {
      const response = await withLoading('settings', () =>
        api.get('/settings')
      );
      settings.value = { ...settings.value, ...response.data };
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to load settings. Please try again later.'
      });
      console.error('Error fetching settings:', error);
    }
  };

  const updateSettings = async (section, data) => {
    try {
      const response = await withLoading('update-settings', () =>
        api.put(`/settings/${section}`, data)
      );

      settings.value[section] = response.data;

      showNotification({
        type: 'success',
        message: 'Settings updated successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to update settings. Please try again.'
      });
      throw error;
    }
  };

  const resetSettings = async (section) => {
    try {
      const response = await withLoading('reset-settings', () =>
        api.post(`/settings/${section}/reset`)
      );

      settings.value[section] = response.data;

      showNotification({
        type: 'success',
        message: 'Settings reset to defaults successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to reset settings. Please try again.'
      });
      throw error;
    }
  };

  const exportSettings = async () => {
    try {
      const response = await withLoading('export-settings', () =>
        api.get('/settings/export', { responseType: 'blob' })
      );

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'settings.json');
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      showNotification({
        type: 'success',
        message: 'Settings exported successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to export settings. Please try again.'
      });
      throw error;
    }
  };

  const importSettings = async (file) => {
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await withLoading('import-settings', () =>
        api.post('/settings/import', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
      );

      settings.value = response.data;

      showNotification({
        type: 'success',
        message: 'Settings imported successfully'
      });

      return response.data;
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to import settings. Please try again.'
      });
      throw error;
    }
  };

  const testEmailSettings = async () => {
    try {
      await withLoading('test-email', () =>
        api.post('/settings/test-email')
      );

      showNotification({
        type: 'success',
        message: 'Test email sent successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to send test email. Please check your email settings.'
      });
      throw error;
    }
  };

  const testSMSSettings = async () => {
    try {
      await withLoading('test-sms', () =>
        api.post('/settings/test-sms')
      );

      showNotification({
        type: 'success',
        message: 'Test SMS sent successfully'
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: 'Failed to send test SMS. Please check your SMS settings.'
      });
      throw error;
    }
  };

  const validatePaymentSettings = async (gateway) => {
    try {
      await withLoading('validate-payment', () =>
        api.post(`/settings/validate-payment/${gateway}`)
      );

      showNotification({
        type: 'success',
        message: `${gateway} settings validated successfully`
      });
    } catch (error) {
      showNotification({
        type: 'error',
        message: `Failed to validate ${gateway} settings. Please check your configuration.`
      });
      throw error;
    }
  };

  return {
    settings,
    isLoading,
    fetchSettings,
    updateSettings,
    resetSettings,
    exportSettings,
    importSettings,
    testEmailSettings,
    testSMSSettings,
    validatePaymentSettings
  };
} 