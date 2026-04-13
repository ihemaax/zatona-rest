import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        sourcemap: false,
    },
    esbuild: {
        drop: ['console', 'debugger'],
        legalComments: 'none',
        minifyIdentifiers: true,
        minifySyntax: true,
        minifyWhitespace: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/front-layout.css',
                'resources/css/pages/front-home.css',
                'resources/css/pages/front-product-show.css',
                'resources/js/app.js',
                'resources/js/pages/front-home.js',
                'resources/js/pages/front-product-show.js',
            ],
            refresh: true,
        }),
    ],
});
