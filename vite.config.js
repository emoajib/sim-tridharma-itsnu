import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '127.0.0.1',
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/react/') || id.includes('node_modules/react-dom/') || id.includes('node_modules/scheduler/')) {
                        return 'vendor';
                    }
                    if (id.includes('node_modules/recharts/')) {
                        return 'recharts';
                    }
                    if (id.includes('node_modules/lucide-react/')) {
                        return 'lucide';
                    }
                },
            },
        },
    },
});
