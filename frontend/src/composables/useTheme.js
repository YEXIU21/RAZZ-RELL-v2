import { ref, watch } from 'vue';

const THEME_KEY = 'razz_rell_theme';
const currentTheme = ref(localStorage.getItem(THEME_KEY) || 'light');

// CSS Variables for themes
const themes = {
  light: {
    '--background-color': '#f8f9fa',
    '--card-background': '#ffffff',
    '--modal-background': '#ffffff',
    '--text-color': '#2c3e50',
    '--text-muted': '#6c757d',
    '--text-secondary': '#6B7280',
    '--border-color': '#dee2e6',
    '--input-background': '#ffffff',
    '--hover-background': '#f3f4f6',
    '--hover-color': '#e5e7eb',
    '--primary-color': '#3b82f6',
    '--primary-hover': '#2563eb',
    '--primary-light': 'rgba(59, 130, 246, 0.1)',
    '--secondary-color': '#64748B',
    '--secondary-dark': '#475569',
    '--success-color': '#22c55e',
    '--success-light': 'rgba(34, 197, 94, 0.1)',
    '--warning-color': '#f59e0b',
    '--warning-light': 'rgba(245, 158, 11, 0.1)',
    '--error-color': '#ef4444',
    '--error-light': 'rgba(239, 68, 68, 0.1)',
    '--info-color': '#3b82f6',
    '--info-light': 'rgba(59, 130, 246, 0.1)',
    '--danger-color': '#DC2626',
    '--danger-light': 'rgba(220, 38, 38, 0.1)',
    '--shadow-sm': '0 1px 2px rgba(0, 0, 0, 0.05)',
    '--shadow-md': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
    '--shadow-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
    '--overlay-background': 'rgba(0, 0, 0, 0.5)',
    '--overlay-dark': 'rgba(0, 0, 0, 0.75)',
    '--table-header-background': 'rgba(0, 0, 0, 0.02)',
    '--event-wedding-bg': '#ffebee',
    '--event-wedding-text': '#c62828',
    '--event-debut-bg': '#fff3e0',
    '--event-debut-text': '#ef6c00',
    '--event-christening-bg': '#e3f2fd',
    '--event-christening-text': '#1565c0',
    '--event-party-bg': '#e8f5e9',
    '--event-party-text': '#2e7d32',
    '--event-platinum-bg': '#ede7f6',
    '--event-platinum-text': '#4527a0',
    '--color-scheme': 'light'
  },
  dark: {
    '--background-color': '#121212',
    '--card-background': '#1E1E1E',
    '--modal-background': '#1E1E1E',
    '--text-color': '#E5E7EB',
    '--text-muted': '#9CA3AF',
    '--text-secondary': '#9CA3AF',
    '--border-color': '#2E2E2E',
    '--input-background': '#2E2E2E',
    '--hover-background': '#2E2E2E',
    '--hover-color': '#363636',
    '--primary-color': '#3B82F6',
    '--primary-hover': '#2563EB',
    '--primary-light': 'rgba(59, 130, 246, 0.1)',
    '--secondary-color': '#64748B',
    '--secondary-dark': '#475569',
    '--success-color': '#22C55E',
    '--success-light': 'rgba(34, 197, 94, 0.1)',
    '--warning-color': '#F59E0B',
    '--warning-light': 'rgba(245, 158, 11, 0.1)',
    '--error-color': '#EF4444',
    '--error-light': 'rgba(239, 68, 68, 0.1)',
    '--info-color': '#3B82F6',
    '--info-light': 'rgba(59, 130, 246, 0.1)',
    '--danger-color': '#DC2626',
    '--danger-light': 'rgba(220, 38, 38, 0.1)',
    '--shadow-sm': '0 1px 2px rgba(0, 0, 0, 0.3)',
    '--shadow-md': '0 4px 6px -1px rgba(0, 0, 0, 0.4)',
    '--shadow-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.5)',
    '--overlay-background': 'rgba(0, 0, 0, 0.75)',
    '--overlay-dark': 'rgba(0, 0, 0, 0.9)',
    '--table-header-background': 'rgba(255, 255, 255, 0.02)',
    '--event-wedding-bg': '#2c1215',
    '--event-wedding-text': '#ff8a80',
    '--event-debut-bg': '#2c1810',
    '--event-debut-text': '#ffb74d',
    '--event-christening-bg': '#0d2137',
    '--event-christening-text': '#64b5f6',
    '--event-party-bg': '#0f2213',
    '--event-party-text': '#81c784',
    '--event-platinum-bg': '#1a1426',
    '--event-platinum-text': '#b39ddb',
    '--color-scheme': 'dark'
  },
};

// Apply theme variables to :root
const applyTheme = (theme) => {
  const root = document.documentElement;
  const themeVariables = themes[theme];

  Object.entries(themeVariables).forEach(([property, value]) => {
    root.style.setProperty(property, value);
  });
};

// Watch for theme changes
watch(currentTheme, (newTheme) => {
  localStorage.setItem(THEME_KEY, newTheme);
  applyTheme(newTheme);
}, { immediate: true });

export function useTheme() {
  const toggleTheme = () => {
    currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light';
  };

  const setTheme = (theme) => {
    if (themes[theme]) {
      currentTheme.value = theme;
    }
  };

  const isDarkMode = () => currentTheme.value === 'dark';

  return {
    currentTheme,
    toggleTheme,
    setTheme,
    isDarkMode,
  };
} 