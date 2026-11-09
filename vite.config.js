import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/auth/auth.css',
                'resources/js/auth/auth.js',
                'resources/css/landing/landing.css',
                'resources/js/landing/landing.js',
                'resources/css/kontak/kontak.css',
                'resources/css/struktur/struktur.css',
                'resources/css/simora/panel/panel.css',
                'resources/css/simora/organization-panel/organization-panel.css',
                'resources/css/simora/organizations/organization-admin.css',
                'resources/css/simora/accounts/account-admin.css',
                'resources/css/simora/module/module.css',
                'resources/css/simora/orgmate/orgmate.css',
                'resources/css/simora/e-progress/e-progress.css',
                'resources/css/simora/sentra/sentra.css',
                'resources/css/simora/theme/simora-theme.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        hmr: {
            host: process.env.VITE_HMR_HOST || undefined,
        },
    },
});
