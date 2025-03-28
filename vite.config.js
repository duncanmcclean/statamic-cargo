import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue2 from '@vitejs/plugin-vue2'
import svgLoader from './vite-svg-loader';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'resources/dist/hot',
            publicDirectory: 'resources/dist',
            input: ['resources/js/cp.js', 'resources/css/cp.css'],
        }),
        vue2(),
        svgLoader(),
    ],
})
