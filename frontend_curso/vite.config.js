import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [react()],
    server: {
      host: '0.0.0.0',
      port: 5173,
      // Caché agresivo de archivos estáticos en dev
      warmup: {
        clientFiles: ['./src/App.jsx', './src/main.jsx'],
      },
      proxy: {
        '/api': {
          target: env.VITE_API_PROXY_TARGET || 'http://api:8000',
          changeOrigin: true,
        },
      },
    },
    build: {
      // Code splitting por ruta para cargar menos JS al inicio
      rollupOptions: {
        output: {
          manualChunks: {
            vendor: ['react', 'react-dom', 'react-router-dom'],
            ui: ['lucide-react', 'axios'],
          },
        },
      },
      // Comprime con terser en producción
      minify: 'esbuild',
      target: 'es2020',
    },
    // Optimiza deps prebuilding
    optimizeDeps: {
      include: ['react', 'react-dom', 'react-router-dom', 'axios', 'lucide-react'],
    },
  }
})
