import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    // Output to the parent public/assets directory to integrate with PHP
    outDir: '../public/assets/react',
    emptyOutDir: true,
    // Generate manifest for PHP to know which files to load
    manifest: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'index.html')
      }
    }
  },
  server: {
    port: 3000,
    // No proxy needed since we're using the actual backend URL
    cors: true
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
})
