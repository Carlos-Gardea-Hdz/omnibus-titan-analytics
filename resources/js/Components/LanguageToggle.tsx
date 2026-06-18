import { useLanguage } from '@/lib/i18n';

export function LanguageToggle() {
    const { locale, toggle, t } = useLanguage();

    return (
        <button
            type="button"
            onClick={toggle}
            aria-label={t('lang.toggle')}
            className="inline-flex h-9 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
        >
            {locale === 'es' ? 'EN' : 'ES'}
        </button>
    );
}
