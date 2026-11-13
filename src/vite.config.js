import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Konfigurasi Vite: membangun CSS (Bootstrap 5.3 + token Ngaksara) dan JavaScript aplikasi.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/latihan/kanvas-menulis.js'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
