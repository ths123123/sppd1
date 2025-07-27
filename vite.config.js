import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/login.css',
                
                // SPPD Form CSS Files
                'resources/css/sppd-form.css',
                'resources/css/sppd-form-animations.css',
                'resources/css/sppd-form-override.css',
                
                'resources/js/app.js',

                // Core JavaScript
                'resources/js/navbar-profile-update.js',

                // Page-specific JavaScript
                'resources/js/pages/dashboard.js',
                'resources/js/pages/analytics.js',
                'resources/js/forms/sppd-form.js',
                'resources/js/sppd-form-enhanced.js',
                'resources/js/dashboard/charts.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@css': '/resources/css',
            '@components': '/resources/js/components',
            '@services': '/resources/js/services',
            '@utils': '/resources/js/utils',
            '@pages': '/resources/js/pages'
        }
    },
    build: {
        outDir: 'public/build',
        manifest: 'manifest.json',
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor chunks
                    vendor: ['alpinejs']
                }
            }
        }
    }
});
