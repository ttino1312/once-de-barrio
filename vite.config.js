import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const raiz = fileURLToPath(new URL('.', import.meta.url))

export default defineConfig({
  base: './',
  plugins: [vue()],
  build: { rollupOptions: { input: { juego: resolve(raiz, 'index.html'), visor: resolve(raiz, 'visor.html'), manual: resolve(raiz, 'manual.html') } } },
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1/once-de-barrio-conectado-api',
        changeOrigin: true,
        rewrite: path => path.replace(/^\/api/, '')
      }
    }
  }
})
