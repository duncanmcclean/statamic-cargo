import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import statamic from '@statamic/cms/vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        statamic(),
        laravel({
            hotFile: 'resources/dist/hot',
            publicDirectory: 'resources/dist',
            input: ['resources/js/cp.js', 'resources/css/cp.css'],
        }),
        tailwindcss(),
    ],
});
