<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <router-link to="/">
            <img src="@/assets/logo2.png" alt="Razz Rell Events" class="auth-logo" />
          </router-link>
          <h1>Set New Password</h1>
          <p>Create a new password for your account</p>
        </div>

        <form @submit.prevent="handleSubmit" class="auth-form">
          <div class="form-group">
            <label for="password">New Password</label>
            <div class="input-group">
              <i class="fas fa-lock"></i>
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="values.password"
                @blur="handleBlur('password')"
                :class="{ 'error': getFieldError('password') }"
                placeholder="Enter new password"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
              >
                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <span class="error-message" v-if="getFieldError('password')">
              {{ getFieldError('password') }}
            </span>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <div class="input-group">
              <i class="fas fa-lock"></i>
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                v-model="values.confirmPassword"
                @blur="handleBlur('confirmPassword')"
                :class="{ 'error': getFieldError('confirmPassword') }"
                placeholder="Confirm new password"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <span class="error-message" v-if="getFieldError('confirmPassword')">
              {{ getFieldError('confirmPassword') }}
            </span>
          </div>

          <div class="password-requirements">
            <h3>Password Requirements:</h3>
            <ul>
              <li :class="{ 'met': hasUpperCase }">
                <i class="fas" :class="hasUpperCase ? 'fa-check' : 'fa-times'"></i>
                At least one uppercase letter
              </li>
              <li :class="{ 'met': hasLowerCase }">
                <i class="fas" :class="hasLowerCase ? 'fa-check' : 'fa-times'"></i>
                At least one lowercase letter
              </li>
              <li :class="{ 'met': hasNumber }">
                <i class="fas" :class="hasNumber ? 'fa-check' : 'fa-times'"></i>
                At least one number
              </li>
              <li :class="{ 'met': hasSpecialChar }">
                <i class="fas" :class="hasSpecialChar ? 'fa-check' : 'fa-times'"></i>
                At least one special character
              </li>
              <li :class="{ 'met': isMinLength }">
                <i class="fas" :class="isMinLength ? 'fa-check' : 'fa-times'"></i>
                Minimum 8 characters
              </li>
            </ul>
          </div>

          <button
            type="submit"
            class="submit-btn"
            :disabled="!isValid || isLoading"
          >
            <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
            <span v-else>Reset Password</span>
          </button>
        </form>

        <div class="auth-footer">
          <p>Remember your password?</p>
          <router-link to="/login" class="login-link">
            Back to login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useValidation } from '@/composables/useValidation';
import { useNotifications } from '@/composables/useNotifications';
import { useLoading } from '@/composables/useLoading';

const route = useRoute();
const router = useRouter();
const { changePassword } = useAuth();
const { success, error: showError } = useNotifications();
const { isLoading, withLoading } = useLoading();

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const {
  values,
  errors,
  touched,
  validate,
  validateAll,
  handleBlur,
  isValid,
  getFieldError,
} = useValidation({
  password: '',
  confirmPassword: '',
});

// Password validation rules
const hasUpperCase = computed(() => /[A-Z]/.test(values.value.password));
const hasLowerCase = computed(() => /[a-z]/.test(values.value.password));
const hasNumber = computed(() => /\d/.test(values.value.password));
const hasSpecialChar = computed(() => /[!@#$%^&*(),.?":{}|<>]/.test(values.value.password));
const isMinLength = computed(() => values.value.password.length >= 8);

const validationRules = {
  password: ['required', 'password'],
  confirmPassword: ['required', ['confirmPassword', values.value.password]],
};

const handleSubmit = async () => {
  if (!validateAll(validationRules)) return;

  try {
    const token = route.query.token;
    const email = route.query.email;
    
    if (!token || !email) {
      showError('Invalid or expired reset token');
      return;
    }

    const success = await withLoading('reset-password', () => 
      changePassword({
        token,
        email,
        password: values.value.password,
        password_confirmation: values.value.confirmPassword
      })
    );

    if (success) {
      router.push('/login');
    }
  } catch (err) {
    showError('Failed to reset password. Please try again.');
  }
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--background-color);
  padding: 2rem;
}

.auth-container {
  width: 100%;
  max-width: 420px;
}

.auth-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-logo {
  width: 120px;
  height: auto;
  margin-bottom: 1.5rem;
}

.auth-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.auth-header p {
  color: var(--text-muted);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
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

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 1rem;
  color: var(--text-muted);
}

.input-group input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.input-group input:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-color-light);
  outline: none;
}

.input-group input.error {
  border-color: var(--error-color);
}

.toggle-password {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
}

.error-message {
  color: var(--error-color);
  font-size: 0.875rem;
}

.password-requirements {
  background: var(--background-color);
  padding: 1rem;
  border-radius: 8px;
}

.password-requirements h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.password-requirements ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.password-requirements li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.password-requirements li i {
  width: 1rem;
}

.password-requirements li.met {
  color: var(--success-color);
}

.submit-btn {
  width: 100%;
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-color-dark);
}

.submit-btn:disabled {
  background: var(--disabled-color);
  cursor: not-allowed;
}

.auth-footer {
  text-align: center;
  margin-top: 1.5rem;
}

.auth-footer p {
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.login-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
}

.login-link:hover {
  text-decoration: underline;
}
</style> 