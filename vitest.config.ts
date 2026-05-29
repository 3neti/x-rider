import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@/components/ui/button': path.resolve(__dirname, 'tests/frontend/stubs/ui-button.ts'),
            'lucide-vue-next': path.resolve(__dirname, 'tests/frontend/stubs/lucide-vue-next.ts'),
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
    },
});