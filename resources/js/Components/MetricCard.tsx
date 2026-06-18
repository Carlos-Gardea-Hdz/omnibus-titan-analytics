interface MetricCardProps {
    label: string;
    value: string;
    hint?: string;
}

export function MetricCard({ label, value, hint }: MetricCardProps) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
            <dt className="text-sm font-medium text-slate-500 dark:text-slate-400">{label}</dt>
            <dd className="mt-2 text-3xl font-semibold tabular-nums text-slate-900 dark:text-slate-50">{value}</dd>
            {hint ? <p className="mt-1 text-xs text-slate-400">{hint}</p> : null}
        </div>
    );
}
