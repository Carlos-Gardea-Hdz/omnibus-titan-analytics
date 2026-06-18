<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The typed payload rendered into the dashboard page. Every Inertia prop shape
 * is a #[TypeScript] DTO (inertia-react-advanced.md §2) — the frontend never
 * redeclares server shapes and never receives raw ClickHouse SQL or raw rows.
 */
#[TypeScript]
final class DashboardSummaryData extends Data
{
    /**
     * @param  array<int, TimeBucketData>  $eventsOverTime
     */
    public function __construct(
        public int $eventsLast24h,
        public int $uniqueVisitors,
        public int $ingestP95Ms,
        public array $eventsOverTime,
    ) {}
}
