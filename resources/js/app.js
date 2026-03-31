import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { createPinia } from 'pinia';
import { configureEcho } from '@laravel/echo-vue';
import { cn } from '@/lib/utils';

// Bootstrap: axios defaults, auth interceptors
import './bootstrap';

// ── Laravel Echo / Reverb ───────────────────────────────────────────────────────
const useReverb = import.meta.env.VITE_USE_REVERB === 'true';

if (useReverb) {
    configureEcho({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'app-key',
        wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
        wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

const pages = import.meta.glob('./Pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} — DeviceMonitor` : 'DeviceMonitor'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.vue`];
        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        return page();
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        app.use(plugin);
        app.use(pinia);
        app.use(ZiggyVue);
        app.config.globalProperties.cn = cn;

        return app.mount(el);
    },
    progress: {
        color: 'var(--color-accent)',
        includeCSS: true,
        showSpinner: true,
    },
    onError: (errors) => {
        console.error('Inertia page error:', errors);
    },
});
