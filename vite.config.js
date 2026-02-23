import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: true,
        origin: 'https://hsmart.test:5173',
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        hmr: {
            host: 'hsmart.test',
            port: 5173,
            protocol: 'wss',
        },
    },
});
