import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['resources/js/**/*.test.ts'],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin-panel/layout.scss',
                'resources/js/admin-panel/layout.ts',
            ],
            hotFile: 'public/hot',
            refresh: [
                'app/Http/Controllers/**/*.php',
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
            ],
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
