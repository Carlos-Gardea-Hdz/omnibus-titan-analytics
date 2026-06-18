<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Enums;

/**
 * Aggregation granularity for dashboard reads. Each value maps to a ClickHouse
 * materialized-view rollup (minute -> hour -> day -> month) so dashboards read
 * pre-aggregated rows, never raw event scans.
 */
enum TimeGranularity: string
{
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';
    case Month = 'month';

    /** ClickHouse rollup function applied to the timestamp column. */
    public function clickhouseFunction(): string
    {
        return match ($this) {
            self::Minute => 'toStartOfMinute',
            self::Hour => 'toStartOfHour',
            self::Day => 'toStartOfDay',
            self::Month => 'toStartOfMonth',
        };
    }
}
