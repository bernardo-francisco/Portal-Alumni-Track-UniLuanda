import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/home.js',
                'resources/js/video-call.js',    
                'resources/js/auth.js',  
                'resources/js/filtros-ajax.js',
                'resources/js/feed.js',
                'resources/js/notificacoes-egresso.js',
                'resources/js/admin-mapa.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
    },
});