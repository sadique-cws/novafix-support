import './bootstrap';

// Inertia + React (runs only on pages that include an #app root)
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

function initDrawer() {
    const openDrawer = (name) => {
        const drawer = document.querySelector(`[data-drawer="${name}"]`);
        const overlay = document.querySelector(`[data-drawer-overlay="${name}"]`);
        if (!drawer || !overlay) return;
        overlay.classList.remove('hidden');
        drawer.classList.remove('-translate-x-full');
        drawer.classList.add('translate-x-0');
        document.documentElement.classList.add('overflow-hidden');
    };

    const closeDrawer = (name) => {
        const drawer = document.querySelector(`[data-drawer="${name}"]`);
        const overlay = document.querySelector(`[data-drawer-overlay="${name}"]`);
        if (!drawer || !overlay) return;
        overlay.classList.add('hidden');
        drawer.classList.add('-translate-x-full');
        drawer.classList.remove('translate-x-0');
        document.documentElement.classList.remove('overflow-hidden');
    };

    document.addEventListener('click', (e) => {
        const open = e.target.closest('[data-drawer-open]');
        if (open) {
            openDrawer(open.getAttribute('data-drawer-open'));
            return;
        }

        const close = e.target.closest('[data-drawer-close]');
        if (close) {
            closeDrawer(close.getAttribute('data-drawer-close'));
            return;
        }

        const overlay = e.target.closest('[data-drawer-overlay]');
        if (overlay) {
            closeDrawer(overlay.getAttribute('data-drawer-overlay'));
        }
    });
}

initDrawer();

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
