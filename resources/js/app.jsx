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

function initModal() {
    const openModal = (name) => {
        const modal = document.querySelector(`[data-modal="${name}"]`);
        const overlay = document.querySelector(`[data-modal-overlay="${name}"]`);
        if (!modal || !overlay) return;
        overlay.classList.remove('hidden');
        modal.classList.remove('hidden');
        document.documentElement.classList.add('overflow-hidden');
    };

    const closeModal = (name) => {
        const modal = document.querySelector(`[data-modal="${name}"]`);
        const overlay = document.querySelector(`[data-modal-overlay="${name}"]`);
        if (!modal || !overlay) return;
        overlay.classList.add('hidden');
        modal.classList.add('hidden');
        document.documentElement.classList.remove('overflow-hidden');
    };

    window.CodexUI = window.CodexUI || {};
    window.CodexUI.openModal = openModal;
    window.CodexUI.closeModal = closeModal;

    document.addEventListener('click', (e) => {
        const open = e.target.closest('[data-modal-open]');
        if (open) {
            openModal(open.getAttribute('data-modal-open'));
            return;
        }

        const close = e.target.closest('[data-modal-close]');
        if (close) {
            closeModal(close.getAttribute('data-modal-close'));
            return;
        }

        const overlay = e.target.closest('[data-modal-overlay]');
        if (overlay) {
            closeModal(overlay.getAttribute('data-modal-overlay'));
        }
    });

    window.addEventListener('open-modal', (e) => {
        if (e?.detail?.name) openModal(e.detail.name);
    });
    window.addEventListener('close-modal', (e) => {
        if (e?.detail?.name) closeModal(e.detail.name);
    });
}

initModal();

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
