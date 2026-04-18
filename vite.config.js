import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                /* app.css is imported from resources/js/app.js — do not list here or manifest lacks resources/css/app.css */
                'resources/js/app.js',
                'resources/css/legacy.css',
                'resources/js/legacy-app.js',
                'resources/css/spa.css',
                'resources/js/spa/main.jsx',
            ],
            refresh: true,
        }),
    ],
});
