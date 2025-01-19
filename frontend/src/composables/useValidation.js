import { ref, computed } from 'vue';

export function useValidation(initialValues = {}) {
  const values = ref({ ...initialValues });
  const errors = ref({});
  const touched = ref({});

  const rules = {
    required: (value) => {
      if (Array.isArray(value)) {
        return value.length > 0 || 'This field is required';
      }
      return !!value || 'This field is required';
    },
    email: (value) => {
      if (!value) return true;
      const emailRegex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i;
      return emailRegex.test(value) || 'Invalid email address';
    },
    minLength: (length) => (value) => {
      if (!value) return true;
      return value.length >= length || `Must be at least ${length} characters`;
    },
    maxLength: (length) => (value) => {
      if (!value) return true;
      return value.length <= length || `Must be at most ${length} characters`;
    },
    matches: (pattern, message) => (value) => {
      if (!value) return true;
      return pattern.test(value) || message;
    },
    min: (min) => (value) => {
      if (!value) return true;
      return Number(value) >= min || `Must be at least ${min}`;
    },
    max: (max) => (value) => {
      if (!value) return true;
      return Number(value) <= max || `Must be at most ${max}`;
    },
    phone: (value) => {
      if (!value) return true;
      const phoneRegex = /^[0-9]{11}$/;
      return phoneRegex.test(value) || 'Phone number must be exactly 11 digits';
    },
    password: (value) => {
      if (!value) return true;
      const hasUpperCase = /[A-Z]/.test(value);
      const hasLowerCase = /[a-z]/.test(value);
      const hasNumbers = /\d/.test(value);
      const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);
      
      if (!hasUpperCase) return 'Password must contain at least one uppercase letter';
      if (!hasLowerCase) return 'Password must contain at least one lowercase letter';
      if (!hasNumbers) return 'Password must contain at least one number';
      if (!hasSpecialChar) return 'Password must contain at least one special character';
      
      return true;
    },
    confirmPassword: (password) => (value) => {
      if (!value) return true;
      return value === password || password;
    },
    url: (value) => {
      if (!value) return true;
      try {
        new URL(value);
        return true;
      } catch {
        return 'Invalid URL';
      }
    },
    date: (value) => {
      if (!value) return true;
      const date = new Date(value);
      return !isNaN(date.getTime()) || 'Invalid date';
    },
    futureDate: (value) => {
      if (!value) return true;
      const date = new Date(value);
      return date > new Date() || 'Date must be in the future';
    },
    pastDate: (value) => {
      if (!value) return true;
      const date = new Date(value);
      return date < new Date() || 'Date must be in the past';
    },
  };

  const validate = (fieldName, validations) => {
    if (!validations) return true;

    for (const validation of validations) {
      const value = values.value[fieldName];
      let rule;

      if (typeof validation === 'string') {
        rule = rules[validation];
      } else if (typeof validation === 'function') {
        rule = validation;
      } else if (Array.isArray(validation)) {
        const [ruleName, ...args] = validation;
        rule = rules[ruleName](...args);
      }

      if (!rule) continue;

      const result = rule(value);
      if (result !== true) {
        errors.value[fieldName] = result;
        return false;
      }
    }

    delete errors.value[fieldName];
    return true;
  };

  const validateAll = (validationRules) => {
    let isValid = true;

    for (const [fieldName, validations] of Object.entries(validationRules)) {
      if (!validate(fieldName, validations)) {
        isValid = false;
      }
    }

    return isValid;
  };

  const handleBlur = (fieldName) => {
    touched.value[fieldName] = true;
  };

  const resetForm = () => {
    values.value = { ...initialValues };
    errors.value = {};
    touched.value = {};
  };

  const setFieldValue = (fieldName, value) => {
    values.value[fieldName] = value;
  };

  const setFieldError = (fieldName, error) => {
    errors.value[fieldName] = error;
  };

  const clearFieldError = (fieldName) => {
    delete errors.value[fieldName];
  };

  const isValid = computed(() => Object.keys(errors.value).length === 0);

  const getFieldError = (fieldName) => {
    return touched.value[fieldName] ? errors.value[fieldName] : '';
  };

  return {
    values,
    errors,
    touched,
    rules,
    validate,
    validateAll,
    handleBlur,
    resetForm,
    setFieldValue,
    setFieldError,
    clearFieldError,
    isValid,
    getFieldError,
  };
} 