import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig(({mode}) => ({
  // В lib-режиме Vite сам не подставляет NODE_ENV, а без него в бандл попадает dev-сборка Vue
  define: {
    'process.env.NODE_ENV': JSON.stringify(mode === 'development' ? 'development' : 'production')
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
}))
