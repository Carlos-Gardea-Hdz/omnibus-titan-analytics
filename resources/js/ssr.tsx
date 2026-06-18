import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';
import { ThemeProvider } from '@/lib/theme';
import { LanguageProvider } from '@/lib/i18n';
import type { ReactElement } from 'react';

const appName = import.meta.env.VITE_APP_NAME ?? 'Titan Analytics';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} · ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
        setup: ({ App, props }) =>
            (
                <ThemeProvider>
                    <LanguageProvider>
                        <App {...props} />
                    </LanguageProvider>
                </ThemeProvider>
            ) as ReactElement,
    }),
);
