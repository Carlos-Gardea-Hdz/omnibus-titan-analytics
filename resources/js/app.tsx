import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { hydrateRoot } from 'react-dom/client';
import { ThemeProvider } from '@/lib/theme';
import { LanguageProvider } from '@/lib/i18n';
import type { ReactElement } from 'react';

const appName = import.meta.env.VITE_APP_NAME ?? 'Titan Analytics';

void createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        const tree = (
            <ThemeProvider>
                <LanguageProvider>
                    <App {...props} />
                </LanguageProvider>
            </ThemeProvider>
        ) as ReactElement;

        // hydrateRoot for SSR parity; falls back gracefully when SSR markup is absent.
        hydrateRoot(el, tree);
    },
    progress: {
        color: '#6366f1',
    },
});
