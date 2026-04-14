import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'


export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/output.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
        VitePWA({
            registerType: 'prompt',
            includeAssets: ['favicon.png', 'pwa-icon.svg'],
            manifest: {
                name: 'SlamHub',
                short_name: 'SlamHub',
                description: 'Platforma pro slam poetry komunitu.',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                background_color: '#0f0f12',
                theme_color: '#0f0f12',
                icons: [
                    {
                        src: '/pwa-icon.svg',
                        sizes: 'any',
                        type: 'image/svg+xml',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                navigateFallback: '/',
                globPatterns: ['**/*.{js,css,html,svg,png,jpg,jpeg,webp,ico,json,txt,woff,woff2}'],
                maximumFileSizeToCacheInBytes: 6 * 1024 * 1024,
            },
            devOptions: {
                enabled: true,
            },
        }),

    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
})