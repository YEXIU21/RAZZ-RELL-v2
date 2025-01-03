<template>
  <div class="settings-section">
    <h2>Integrations Settings</h2>
    <p class="section-description">
      Configure third-party integrations and services
    </p>

    <form @submit.prevent="saveSettings" class="settings-form">
      <div class="settings-group">
        <h3>Payment Gateways</h3>
        
        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fab fa-stripe"></i>
              <div>
                <h4>Stripe</h4>
                <p>Process credit card payments securely</p>
              </div>
            </div>
            <div class="integration-toggle">
              <label class="switch">
                <input
                  type="checkbox"
                  v-model="form.paymentGateways.stripe.enabled"
                />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div 
            class="integration-content"
            v-show="form.paymentGateways.stripe.enabled"
          >
            <div class="form-row">
              <div class="form-group">
                <label>Public Key</label>
                <input
                  type="text"
                  v-model="form.paymentGateways.stripe.publicKey"
                  placeholder="pk_test_..."
                />
              </div>

              <div class="form-group">
                <label>Secret Key</label>
                <input
                  type="password"
                  v-model="form.paymentGateways.stripe.secretKey"
                  placeholder="sk_test_..."
                />
              </div>
            </div>

            <div class="form-group checkbox-group">
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.paymentGateways.stripe.testMode"
                />
                Test Mode
              </label>
              <span class="help-text">
                Use test credentials for development
              </span>
            </div>

            <div class="integration-actions">
              <button 
                type="button"
                class="test-btn"
                @click="validatePayment('stripe')"
                :disabled="!form.paymentGateways.stripe.enabled || isValidating.stripe"
              >
                <i class="fas fa-spinner fa-spin" v-if="isValidating.stripe"></i>
                <span v-else>Validate Credentials</span>
              </button>
            </div>
          </div>
        </div>

        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fab fa-paypal"></i>
              <div>
                <h4>PayPal</h4>
                <p>Accept PayPal payments</p>
              </div>
            </div>
            <div class="integration-toggle">
              <label class="switch">
                <input
                  type="checkbox"
                  v-model="form.paymentGateways.paypal.enabled"
                />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div 
            class="integration-content"
            v-show="form.paymentGateways.paypal.enabled"
          >
            <div class="form-row">
              <div class="form-group">
                <label>Client ID</label>
                <input
                  type="text"
                  v-model="form.paymentGateways.paypal.clientId"
                  placeholder="client_id..."
                />
              </div>

              <div class="form-group">
                <label>Client Secret</label>
                <input
                  type="password"
                  v-model="form.paymentGateways.paypal.clientSecret"
                  placeholder="client_secret..."
                />
              </div>
            </div>

            <div class="form-group checkbox-group">
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.paymentGateways.paypal.testMode"
                />
                Sandbox Mode
              </label>
              <span class="help-text">
                Use sandbox environment for testing
              </span>
            </div>

            <div class="integration-actions">
              <button 
                type="button"
                class="test-btn"
                @click="validatePayment('paypal')"
                :disabled="!form.paymentGateways.paypal.enabled || isValidating.paypal"
              >
                <i class="fas fa-spinner fa-spin" v-if="isValidating.paypal"></i>
                <span v-else>Validate Credentials</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Email Service</h3>
        
        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fas fa-envelope"></i>
              <div>
                <h4>SMTP Configuration</h4>
                <p>Configure email sending service</p>
              </div>
            </div>
          </div>

          <div class="integration-content">
            <div class="form-row">
              <div class="form-group">
                <label>SMTP Host</label>
                <input
                  type="text"
                  v-model="form.emailService.host"
                  placeholder="smtp.example.com"
                />
              </div>

              <div class="form-group">
                <label>SMTP Port</label>
                <input
                  type="text"
                  v-model="form.emailService.port"
                  placeholder="587"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Username</label>
                <input
                  type="text"
                  v-model="form.emailService.username"
                  placeholder="username"
                />
              </div>

              <div class="form-group">
                <label>Password</label>
                <input
                  type="password"
                  v-model="form.emailService.password"
                  placeholder="password"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Encryption</label>
              <select v-model="form.emailService.encryption">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="none">None</option>
              </select>
            </div>

            <div class="integration-actions">
              <button 
                type="button"
                class="test-btn"
                @click="testEmail"
                :disabled="isTestingEmail"
              >
                <i class="fas fa-spinner fa-spin" v-if="isTestingEmail"></i>
                <span v-else>Send Test Email</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>SMS Service</h3>
        
        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fas fa-sms"></i>
              <div>
                <h4>Twilio Configuration</h4>
                <p>Configure SMS sending service</p>
              </div>
            </div>
          </div>

          <div class="integration-content">
            <div class="form-row">
              <div class="form-group">
                <label>Account SID</label>
                <input
                  type="text"
                  v-model="form.smsService.accountSid"
                  placeholder="AC..."
                />
              </div>

              <div class="form-group">
                <label>Auth Token</label>
                <input
                  type="password"
                  v-model="form.smsService.authToken"
                  placeholder="auth_token"
                />
              </div>
            </div>

            <div class="form-group">
              <label>From Number</label>
              <input
                type="text"
                v-model="form.smsService.fromNumber"
                placeholder="+1234567890"
              />
            </div>

            <div class="integration-actions">
              <button 
                type="button"
                class="test-btn"
                @click="testSMS"
                :disabled="isTestingSMS"
              >
                <i class="fas fa-spinner fa-spin" v-if="isTestingSMS"></i>
                <span v-else>Send Test SMS</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Maps Integration</h3>
        
        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fas fa-map-marked-alt"></i>
              <div>
                <h4>Google Maps</h4>
                <p>Display maps and location services</p>
              </div>
            </div>
            <div class="integration-toggle">
              <label class="switch">
                <input
                  type="checkbox"
                  v-model="form.googleMaps.enabled"
                />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div 
            class="integration-content"
            v-show="form.googleMaps.enabled"
          >
            <div class="form-group">
              <label>API Key</label>
              <input
                type="text"
                v-model="form.googleMaps.apiKey"
                placeholder="AIza..."
              />
            </div>
          </div>
        </div>
      </div>

      <div class="settings-group">
        <h3>Analytics</h3>
        
        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fas fa-chart-line"></i>
              <div>
                <h4>Google Analytics</h4>
                <p>Track website traffic and user behavior</p>
              </div>
            </div>
            <div class="integration-toggle">
              <label class="switch">
                <input
                  type="checkbox"
                  v-model="form.analytics.googleAnalytics.enabled"
                />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div 
            class="integration-content"
            v-show="form.analytics.googleAnalytics.enabled"
          >
            <div class="form-group">
              <label>Tracking ID</label>
              <input
                type="text"
                v-model="form.analytics.googleAnalytics.trackingId"
                placeholder="G-XXXXXXXXXX"
              />
            </div>
          </div>
        </div>

        <div class="integration-card">
          <div class="integration-header">
            <div class="integration-info">
              <i class="fab fa-facebook"></i>
              <div>
                <h4>Facebook Pixel</h4>
                <p>Track website conversions</p>
              </div>
            </div>
            <div class="integration-toggle">
              <label class="switch">
                <input
                  type="checkbox"
                  v-model="form.analytics.facebookPixel.enabled"
                />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div 
            class="integration-content"
            v-show="form.analytics.facebookPixel.enabled"
          >
            <div class="form-group">
              <label>Pixel ID</label>
              <input
                type="text"
                v-model="form.analytics.facebookPixel.pixelId"
                placeholder="XXXXXXXXXX"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button 
          type="button" 
          class="secondary-btn"
          @click="resetForm"
        >
          Reset
        </button>
        <button 
          type="submit" 
          class="primary-btn"
          :disabled="isLoading"
        >
          <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSettings } from '@/composables/useSettings';
import { useConfirmation } from '@/composables/useConfirmation';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'save']);

const { 
  isLoading, 
  updateSettings, 
  resetSettings,
  testEmailSettings,
  testSMSSettings,
  validatePaymentSettings
} = useSettings();

const { showConfirmation } = useConfirmation();

const isTestingEmail = ref(false);
const isTestingSMS = ref(false);
const isValidating = ref({
  stripe: false,
  paypal: false
});

const form = ref({
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
});

const initForm = () => {
  form.value = { ...props.modelValue };
};

const saveSettings = async () => {
  try {
    await updateSettings('integrations', form.value);
    emit('update:modelValue', { ...form.value });
    emit('save');
  } catch (error) {
    console.error('Error saving settings:', error);
  }
};

const resetForm = async () => {
  const confirmed = await showConfirmation({
    title: 'Reset Settings',
    message: 'Are you sure you want to reset integration settings to their default values? This action cannot be undone.',
    confirmText: 'Reset Settings',
    cancelText: 'Cancel'
  });

  if (confirmed) {
    try {
      const defaults = await resetSettings('integrations');
      form.value = { ...defaults };
      emit('update:modelValue', { ...defaults });
    } catch (error) {
      console.error('Error resetting settings:', error);
    }
  }
};

const testEmail = async () => {
  isTestingEmail.value = true;
  try {
    await testEmailSettings();
  } catch (error) {
    console.error('Error testing email:', error);
  } finally {
    isTestingEmail.value = false;
  }
};

const testSMS = async () => {
  isTestingSMS.value = true;
  try {
    await testSMSSettings();
  } catch (error) {
    console.error('Error testing SMS:', error);
  } finally {
    isTestingSMS.value = false;
  }
};

const validatePayment = async (gateway) => {
  isValidating.value[gateway] = true;
  try {
    await validatePaymentSettings(gateway);
  } catch (error) {
    console.error(`Error validating ${gateway}:`, error);
  } finally {
    isValidating.value[gateway] = false;
  }
};

// Watch for external changes
watch(() => props.modelValue, initForm, { deep: true });

// Initialize form
onMounted(initForm);
</script>

<style scoped>
.settings-section {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
}

h2 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

h4 {
  font-size: 1.125rem;
  color: var(--text-color);
  margin-bottom: 0.25rem;
}

.section-description {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.settings-form {
  display: grid;
  gap: 2rem;
}

.settings-group {
  display: grid;
  gap: 1rem;
}

.integration-card {
  background: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
}

.integration-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--card-background);
  border-bottom: 1px solid var(--border-color);
}

.integration-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.integration-info i {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--background-color);
  border-radius: 8px;
}

.integration-info p {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.integration-content {
  padding: 1rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background-color);
  color: var(--text-color);
}

.help-text {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.checkbox-group {
  margin-top: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  color: var(--text-color);
  font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  border: 2px solid var(--border-color);
  border-radius: 4px;
  cursor: pointer;
}

.integration-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.test-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  background: var(--background-color);
  border: 1px solid var(--border-color);
  color: var(--text-color);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.test-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--border-color);
  transition: 0.4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: var(--primary-color);
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.form-actions button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.secondary-btn {
  background: none;
  border: 1px solid var(--border-color);
  color: var(--text-color);
}

.primary-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.primary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.fa-stripe { color: #6772e5; }
.fa-paypal { color: #003087; }
.fa-envelope { color: #ea4335; }
.fa-sms { color: #34b7f1; }
.fa-map-marked-alt { color: #4285f4; }
.fa-chart-line { color: #ff6d00; }
.fa-facebook { color: #1877f2; }

@media (max-width: 768px) {
  .settings-section {
    padding: 1.5rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .integration-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .integration-info {
    flex-direction: column;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
    justify-content: center;
  }
}
</style> 