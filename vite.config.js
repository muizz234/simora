import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

// Tailwind sengaja tidak dimuat: tidak ada kelas utility Tailwind di Blade/CSS
// (semua styling memakai CSS kustom per modul di resources/css).
// Tambahkan kembali `tailwindcss()` bila utility Tailwind mulai dipakai.

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
                'resources/js/simora/module.js',
                'resources/js/clock.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
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
