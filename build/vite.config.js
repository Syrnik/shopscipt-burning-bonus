import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  define: {
    'process.env': {}
  },
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './notifications')
    }
  },
  build: {
    lib: {
      entry: './notifications/main.js',
      name: 'BurningbonusNotificationsSettings',
      fileName: (format) => `notifications-settings.${format === 'umd' ? 'js' : format}.js`,
      formats: ['umd']
    },
    outDir: '../js',
    target: 'es2015',
    rollupOptions: {
      external: [],
      output: {
        dir: '../js',
        entryFileNames: 'notifications-settings.js',
        format: 'umd',
        name: 'BurningbonusNotificationsSettings',
        globals: {}
      }
    },
    minify: 'terser',
    terserOptions: {
      compress: true,
      mangle: true,
      output: {
        comments: false
      }
    }
  }
})
