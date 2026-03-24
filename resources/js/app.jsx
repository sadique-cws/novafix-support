import './bootstrap';

// Inertia + React (runs only on pages that include an #app root)
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const inertiaRoot = document.getElementById('app');

if (inertiaRoot) {
    createInertiaApp({
        resolve: (name) =>
            resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
        setup({ el, App, props }) {
            createRoot(el).render(<App {...props} />);
        },
    });
}
