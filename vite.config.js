import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import svgLoader from 'vite-svg-loader';
import { viteExternalsPlugin } from 'vite-plugin-externals';
import statamic from './vendor/statamic/cms/resources/js/vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        statamic(),
        laravel({
            hotFile: 'resources/dist/hot',
            publicDirectory: 'resources/dist',
            input: ['resources/js/cp.js', 'resources/css/cp.css'],
        }),
        vue(),
        viteExternalsPlugin({ vue: 'Vue', pinia: 'Pinia', 'vue-demi': 'VueDemi' }),
        svgLoader(),
        tailwindcss(),
    ],
});
