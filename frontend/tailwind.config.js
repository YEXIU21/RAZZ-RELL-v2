/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
      },
      animation: {
        'slide-up': 'slideUp 0.3s ease-out',
        'slide-down': 'slideDown 0.3s ease-out',
        'fade-in': 'fadeIn 0.2s ease-out',
        'fade-out': 'fadeOut 0.2s ease-out',
        'scale-in': 'scaleIn 0.2s ease-out',
        'scale-out': 'scaleOut 0.2s ease-out',
        'spin-slow': 'spin 3s linear infinite',
        'bounce-slow': 'bounce 3s infinite',
        'pulse-slow': 'pulse 3s infinite',
        'float': 'float 3s ease-in-out infinite',
        'about-pattern': 'about-pattern 20s linear infinite',
        'team-social': 'team-social 0.3s ease-out',
        'value-hover': 'value-hover 0.3s ease-out',
        'stat-count': 'stat-count 2s ease-out',
        'mission-skew': 'mission-skew 0.5s ease-out',
      },
      keyframes: {
        'about-pattern': {
          '0%': { transform: 'translateX(0) translateY(0)' },
          '100%': { transform: 'translateX(-100%) translateY(-100%)' },
        },
        'team-social': {
          '0%': { transform: 'translateX(1rem)', opacity: '0' },
          '100%': { transform: 'translateX(0)', opacity: '1' },
        },
        'value-hover': {
          '0%': { transform: 'translateY(0)' },
          '100%': { transform: 'translateY(-0.25rem)' },
        },
        'stat-count': {
          '0%': { opacity: '0', transform: 'translateY(1rem)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'mission-skew': {
          '0%': { transform: 'skewY(0deg)' },
          '100%': { transform: 'skewY(-6deg)' },
        },
      },
      backgroundImage: {
        'about-pattern': 'url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M54.627 0l.83.828-1.415 1.415L51.8 0h2.827zM5.373 0l-.83.828L5.96 2.243 8.2 0H5.374zM48.97 0l3.657 3.657-1.414 1.414L46.143 0h2.828zM11.03 0L7.372 3.657 8.787 5.07 13.857 0H11.03zm32.284 0L49.8 6.485 48.384 7.9l-7.9-7.9h2.83zM16.686 0L10.2 6.485 11.616 7.9l7.9-7.9h-2.83zM37.656 0l8.485 8.485-1.414 1.414L36.242 1.414 37.656 0zM22.344 0L13.858 8.485 15.272 9.9l8.485-8.485L22.344 0zM32.4 0l10.142 10.142-1.414 1.414L30.986 1.414 32.4 0zM27.6 0L17.458 10.142l1.414 1.414L29.014 1.414 27.6 0zM35.785 0l11.314 11.314-1.414 1.414L33.37 1.414 35.785 0zM24.215 0L12.9 11.314l1.414 1.414L25.63 1.414 24.214 0zM30 0l12.485 12.485-1.414 1.415L30 2.828 18.928 13.9l-1.414-1.415L30 0zm2.828 0l13.657 13.657-1.414 1.414L32.828 2.828 30 5.657 27.172 2.828 14.93 15.07l-1.414-1.414L27.172 0h5.656zM39.9 16.385L56.285 0h2.828L39.9 19.214v-2.83zm-9.9 3.714L50.385 0h2.828L30 23.214v-3.115zm-9.9-3.714L0 35.9v-2.828L20.1 13.557v2.828zm9.9 3.714L0 40.385v-2.828L30 20.1v3.115zm-10.607 6.11L0 44.9v-2.828L19.293 22.78l1.414 1.414zM36.9 20.1L56.385 0h2.828L39.9 19.214v2.828L0 49.385v-2.828L36.9 20.1zm-6.785 3.715L56.385 0h2.828L30 26.214v-2.828L0 54.385v-2.828l30.115-27.742zm-9.9 3.714L0 59.385v-2.828L20.215 24.215l1.414 1.414zm10.607 6.11L0 64.385v-2.828L30.922 34.02l1.414 1.414zm9.9-3.714L56.385 0h2.828L41.522 27.628l-1.414-1.414zM46.8 20.1L56.385 0h2.828L46.8 22.928v-2.828zm3.714 9.9L56.385 0h2.828L50.514 29.514v2.828L0 69.385v-2.828L50.514 32.342z\' fill=\'%23ffffff\' fill-opacity=\'0.1\' fill-rule=\'evenodd\'/%3E%3C/svg%3E")',
        'team-gradient': 'linear-gradient(to top, rgba(0,0,0,0.8), transparent)',
        'stats-gradient': 'linear-gradient(135deg, var(--tw-gradient-stops))',
      },
      minHeight: {
        'hero': '60vh',
        'about-section': '40vh',
      },
      aspectRatio: {
        'team': '3/4',
        'about': '4/3',
      },
      skew: {
        'mission': '-6deg',
      },
      transitionDelay: {
        '0': '0ms',
        '100': '100ms',
        '200': '200ms',
        '300': '300ms',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
} 