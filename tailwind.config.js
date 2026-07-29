/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/js/**/*.vue',
    './Modules/**/Resources/views/**/*.php',
    './Modules/**/Resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        vazir: ['Vazirmatn', 'sans-serif'],
      },
      colors: {
        canvas: '#eef6f4',
        ink: { 900: '#0f2b27', 700: '#1c3d38', 500: '#5c7d78', 400: '#8aa6a1' },
        brand: { 50: '#e9faf6', 100: '#cdf3ea', 300: '#7fded0', 500: '#12a894', 600: '#0d8f7e', 700: '#0a7266', 800: '#0a5c53' },
      },
      boxShadow: {
        card: '0 20px 60px -15px rgba(10,89,80,0.18)',
        soft: '0 10px 30px -8px rgba(10,89,80,0.12)',
      },
    },
  },
  plugins: [],
};
