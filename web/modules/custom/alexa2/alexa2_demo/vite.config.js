import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react-swc';
import babel from 'vite-plugin-babel';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react(), babel()],
  resolve: {
    alias: [
      { find: '@', replacement: path.resolve(__dirname, 'js/src') },
    ],
  },
  build: {
    outDir: 'js/dist',
    lib: {
      entry: path.resolve(__dirname, 'js/src/main.tsx'),
      name: 'WizardCMS',
    },
  },
  define: {
    'process.env': {},
  },
});
