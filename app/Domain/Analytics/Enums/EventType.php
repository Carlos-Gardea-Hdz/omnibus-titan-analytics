<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Enums;

/**
 * Bounded set of accepted analytics event types.
 *
 * Stored as LowCardinality(String) in ClickHouse. An allowlist enum (default deny)
 * keeps hostile payloads from exploding the column cardinality — never accept a
 * free-form event type string from the edge.
 */
enum EventType: string
{
    case PageView = 'page_view';
    case Click = 'click';
    case Custom = 'custom';
    case Conversion = 'conversion';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Page view',
            self::Click => 'Click',
            self::Custom => 'Custom',
            self::Conversion => 'Conversion',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PageView => '#4f46e5',
            self::Click => '#0ea5e9',
            self::Custom => '#64748b',
            self::Conversion => '#16a34a',
        };
    }
}
