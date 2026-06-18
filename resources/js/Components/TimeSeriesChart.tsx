import { useId } from 'react';
import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useTheme } from '@/lib/theme';
import { useLanguage } from '@/lib/i18n';
import type { TimeBucket } from '@/types';

interface TimeSeriesChartProps {
    title: string;
    data: ReadonlyArray<TimeBucket>;
}

/**
 * Accessible time-series chart (analytics-data.md "Dashboards — UI/UX & Accessibility").
 * - Theme-aware palette (dark/light), never color as the only encoding.
 * - Always ships a visually-hidden data-table fallback for screen readers.
 * - role="img" with an accessible name; the table is the text alternative.
 */
export function TimeSeriesChart({ title, data }: TimeSeriesChartProps) {
    const { theme } = useTheme();
    const { t } = useLanguage();
    const tableId = useId();

    const stroke = theme === 'dark' ? '#818cf8' : '#4f46e5';
    const grid = theme === 'dark' ? '#334155' : '#e2e8f0';
    const axis = theme === 'dark' ? '#94a3b8' : '#475569';

    return (
        <figure className="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
            <figcaption className="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                {title}
            </figcaption>

            <div role="img" aria-label={title} aria-describedby={tableId} className="h-64 w-full">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={data as TimeBucket[]} margin={{ top: 8, right: 8, bottom: 0, left: -16 }}>
                        <CartesianGrid stroke={grid} strokeDasharray="3 3" />
                        <XAxis dataKey="bucket" stroke={axis} fontSize={12} tickMargin={8} />
                        <YAxis stroke={axis} fontSize={12} allowDecimals={false} />
                        <Tooltip
                            contentStyle={{
                                background: theme === 'dark' ? '#0f172a' : '#ffffff',
                                border: `1px solid ${grid}`,
                                borderRadius: 8,
                                color: axis,
                            }}
                        />
                        <Area type="monotone" dataKey="count" stroke={stroke} fill={stroke} fillOpacity={0.15} />
                    </AreaChart>
                </ResponsiveContainer>
            </div>

            {/* Visually-hidden data table: text alternative required for every chart. */}
            <table id={tableId} className="sr-only">
                <caption>{t('chart.tableAlt')}</caption>
                <thead>
                    <tr>
                        <th scope="col">{t('time.hour')}</th>
                        <th scope="col">{t('time.count')}</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((row) => (
                        <tr key={row.bucket}>
                            <th scope="row">{row.bucket}</th>
                            <td>{row.count}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </figure>
    );
}
