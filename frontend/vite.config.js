import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: '/crm/',
  build: {
    outDir: '../public/crm',
    emptyOutDir: true,
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'https://abhushancrm.test',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
