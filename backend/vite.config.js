import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin-hr.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/css/create-application.css',
                'resources/css/employee.css',
                'resources/css/auth.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
