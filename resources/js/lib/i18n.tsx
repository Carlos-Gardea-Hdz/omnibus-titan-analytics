import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';

export type Locale = 'es' | 'en';

/**
 * Bilingual UI copy (ES/EN) — project law for all user-facing strings.
 * Keep keys flat and namespaced by surface (e.g. `dashboard.title`).
 */
const messages = {
    es: {
        'app.name': 'Titan Analytics',
        'nav.dashboard': 'Panel',
        'nav.events': 'Eventos',
        'nav.privacy': 'Privacidad',
        'dashboard.title': 'Panel de Analítica',
        'dashboard.subtitle': 'Métricas agregadas en tiempo casi real desde el almacén columnar.',
        'metric.events': 'Eventos (24 h)',
        'metric.visitors': 'Visitantes únicos',
        'metric.p95': 'Latencia de ingesta p95',
        'chart.eventsOverTime': 'Eventos por hora',
        'chart.tableAlt': 'Tabla de datos del gráfico',
        'theme.toggle': 'Cambiar tema claro/oscuro',
        'lang.toggle': 'Cambiar idioma',
        'time.hour': 'Hora',
        'time.count': 'Conteo',
    },
    en: {
        'app.name': 'Titan Analytics',
        'nav.dashboard': 'Dashboard',
        'nav.events': 'Events',
        'nav.privacy': 'Privacy',
        'dashboard.title': 'Analytics Dashboard',
        'dashboard.subtitle': 'Near real-time aggregated metrics from the columnar store.',
        'metric.events': 'Events (24h)',
        'metric.visitors': 'Unique visitors',
        'metric.p95': 'Ingest p95 latency',
        'chart.eventsOverTime': 'Events per hour',
        'chart.tableAlt': 'Chart data table',
        'theme.toggle': 'Toggle light/dark theme',
        'lang.toggle': 'Toggle language',
        'time.hour': 'Hour',
        'time.count': 'Count',
    },
} as const;

export type TranslationKey = keyof (typeof messages)['en'];

interface LanguageContextValue {
    locale: Locale;
    t: (key: TranslationKey) => string;
    setLocale: (locale: Locale) => void;
    toggle: () => void;
}

const LanguageContext = createContext<LanguageContextValue | null>(null);

const STORAGE_KEY = 'titan.locale';

function resolveInitialLocale(): Locale {
    if (typeof window === 'undefined') {
        return 'es';
    }
    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (stored === 'es' || stored === 'en') {
        return stored;
    }
    return window.navigator.language.startsWith('en') ? 'en' : 'es';
}

export function LanguageProvider({ children }: { children: ReactNode }) {
    const [locale, setLocaleState] = useState<Locale>(resolveInitialLocale);

    useEffect(() => {
        if (typeof document === 'undefined') {
            return;
        }
        document.documentElement.lang = locale;
        window.localStorage.setItem(STORAGE_KEY, locale);
    }, [locale]);

    const t = useCallback((key: TranslationKey) => messages[locale][key] ?? key, [locale]);
    const setLocale = useCallback((next: Locale) => setLocaleState(next), []);
    const toggle = useCallback(() => setLocaleState((l) => (l === 'es' ? 'en' : 'es')), []);

    const value = useMemo<LanguageContextValue>(
        () => ({ locale, t, setLocale, toggle }),
        [locale, t, setLocale, toggle],
    );

    return <LanguageContext.Provider value={value}>{children}</LanguageContext.Provider>;
}

export function useLanguage(): LanguageContextValue {
    const ctx = useContext(LanguageContext);
    if (!ctx) {
        throw new Error('useLanguage must be used within a LanguageProvider');
    }
    return ctx;
}
