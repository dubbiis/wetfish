import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            publicDirectory: 'public_html',
        }),
    ],
    build: {
        outDir: 'public_html/build',
        manifest: 'manifest.json',
    },
});
