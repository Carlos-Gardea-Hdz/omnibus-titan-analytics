import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { ThemeToggle } from '@/Components/ThemeToggle';
import { LanguageToggle } from '@/Components/LanguageToggle';
import { useLanguage } from '@/lib/i18n';

export default function DashboardLayout({ children }: { children: ReactNode }) {
    const { t } = useLanguage();

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100">
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-indigo-600 focus:px-4 focus:py-2 focus:text-white"
            >
                Skip to content
            </a>

            <header className="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                    <div className="flex items-center gap-6">
                        <span className="text-lg font-bold tracking-tight">{t('app.name')}</span>
                        <nav aria-label="Primary" className="hidden gap-4 text-sm font-medium sm:flex">
                            <Link href="/dashboard" className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                {t('nav.dashboard')}
                            </Link>
                            <Link href="/events" className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                {t('nav.events')}
                            </Link>
                            <Link href="/privacy" className="hover:text-indigo-600 dark:hover:text-indigo-400">
                                {t('nav.privacy')}
                            </Link>
                        </nav>
                    </div>
                    <div className="flex items-center gap-2">
                        <LanguageToggle />
                        <ThemeToggle />
                    </div>
                </div>
            </header>

            <main id="main" className="mx-auto max-w-7xl px-4 py-8">
                {children}
            </main>
        </div>
    );
}
