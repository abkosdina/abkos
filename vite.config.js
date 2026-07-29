import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/css/landing.css',
        'resources/js/app.js',
        'resources/js/landing.js',
      ],
      refresh: ['resources/views/**'],
    }),
  ],
  build: {
    sourcemap: false,
    cssCodeSplit: true,
    chunkSizeWarningLimit: 1000,
  },
  server: {
    watch: {
      ignored: ['**/vendor/**', '**/storage/**', '**/node_modules/**'],
    },
  },
});
