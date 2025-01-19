<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <router-link to="/">
            <img src="@/assets/logo2.png" alt="Razz Rell Events" class="auth-logo" />
          </router-link>
          <h1>Reset Password</h1>
          <p>Enter your email to receive password reset instructions</p>
        </div>

        <form @submit.prevent="handleSubmit" class="auth-form">
          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-group">
              <i class="fas fa-envelope"></i>
              <input
                type="email"
                id="email"
                v-model="email"
                :class="{ 'error': emailError }"
                placeholder="Enter your email"
                autocomplete="email"
                required
              />
            </div>
            <span class="error-message" v-if="emailError">{{ emailError }}</span>
          </div>

          <button type="submit" class="submit-btn" :disabled="!email || isLoading">
            {{ isLoading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>

        <div class="auth-footer">
          <p>Remember your password?</p>
          <router-link to="/login" class="login-link">Back to login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const { forgotPassword } = useAuth();

const email = ref('');
const emailError = ref('');
const isLoading = ref(false);

const handleSubmit = async () => {
  try {
    emailError.value = '';
    isLoading.value = true;

    if (!email.value) {
      emailError.value = 'Email is required';
      return;
    }

    const success = await forgotPassword(email.value);
    if (success) {
      router.push('/login');
    }
  } catch (error) {
    console.error('Error in handleSubmit:', error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
  padding: 20px;
}

.auth-container {
  width: 100%;
  max-width: 400px;
}

.auth-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-logo {
  height: 60px;
  margin-bottom: 1rem;
}

.auth-header h1 {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.auth-header p {
  color: #666;
  font-size: 0.9rem;
}

.auth-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 12px;
  color: #666;
}

input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

input.error {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.8rem;
  margin-top: 0.25rem;
  display: block;
}

.submit-btn {
  width: 100%;
  padding: 0.75rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #0056b3;
}

.submit-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.auth-footer {
  text-align: center;
}

.auth-footer p {
  margin-bottom: 0.5rem;
  color: #666;
}

.login-link {
  color: #007bff;
  text-decoration: none;
}

.login-link:hover {
  text-decoration: underline;
}
</style> 