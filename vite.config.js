import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: [
                'resources/views/**',
                'Modules/**/resources/views/**',
                'Modules/**/routes/**',
                'Modules/**/app/**',
            ],
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return;
                    }

                    if (id.includes('@fortawesome/fontawesome-free')) {
                        return 'fontawesome';
                    }

                    if (id.includes('alpinejs')) {
                        return 'alpine';
                    }

                    if (id.includes('axios')) {
                        return 'axios';
                    }
                },
            },
        },
    },
});
