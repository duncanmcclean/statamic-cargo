import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'resources/dist-checkout/hot',
            publicDirectory: 'resources/dist-checkout',
            input: ['resources/css/checkout.css'],
        }),
        tailwindcss(),
    ],
});
