import { Head } from '@inertiajs/react';
import DashboardLayout from '@/Layouts/DashboardLayout';
import { MetricCard } from '@/Components/MetricCard';
import { TimeSeriesChart } from '@/Components/TimeSeriesChart';
import { useLanguage } from '@/lib/i18n';
import type { TimeBucket } from '@/types';
import type { ReactElement } from 'react';

interface DashboardProps {
    eventsLast24h: number;
    uniqueVisitors: number;
    ingestP95Ms: number;
    eventsOverTime: ReadonlyArray<TimeBucket>;
}

export default function Dashboard({ eventsLast24h, uniqueVisitors, ingestP95Ms, eventsOverTime }: DashboardProps) {
    const { t } = useLanguage();

    return (
        <>
            <Head title={t('dashboard.title')} />

            <div className="space-y-8">
                <header>
                    <h1 className="text-2xl font-bold tracking-tight">{t('dashboard.title')}</h1>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{t('dashboard.subtitle')}</p>
                </header>

                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <MetricCard label={t('metric.events')} value={eventsLast24h.toLocaleString()} />
                    <MetricCard label={t('metric.visitors')} value={uniqueVisitors.toLocaleString()} />
                    <MetricCard label={t('metric.p95')} value={`${ingestP95Ms} ms`} />
                </dl>

                <TimeSeriesChart title={t('chart.eventsOverTime')} data={eventsOverTime} />
            </div>
        </>
    );
}

Dashboard.layout = (page: ReactElement) => <DashboardLayout>{page}</DashboardLayout>;
