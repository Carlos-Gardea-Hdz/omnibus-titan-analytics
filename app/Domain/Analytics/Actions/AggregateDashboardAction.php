<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Actions;

use App\Domain\Analytics\Data\DashboardSummaryData;
use App\Domain\Analytics\Enums\TimeGranularity;
use App\Domain\Analytics\Repositories\EventStoreRepository;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Query stage: build the typed dashboard summary from MV rollups.
 *
 * Reads pre-aggregated rollups (never raw event scans), caps the time range,
 * and caches the result in Valkey with a short TTL. Returns a #[TypeScript] DTO
 * — no ClickHouse SQL or raw rows ever reach the browser.
 */
final readonly class AggregateDashboardAction
{
    private const MAX_RANGE_HOURS = 24 * 90; // hard cap: 90 days

    public function __construct(
        private EventStoreRepository $store,
        private CacheRepository $cache,
    ) {}

    public function handle(string $projectId, CarbonImmutable $from, CarbonImmutable $to): DashboardSummaryData
    {
        // Enforce a bounded time range (never an unbounded scan).
        if ($from->diffInHours($to) > self::MAX_RANGE_HOURS) {
            $from = $to->subHours(self::MAX_RANGE_HOURS);
        }

        $cacheKey = sprintf(
            'analytics:v1:dashboard:%s:%d:%d',
            $projectId,
            $from->getTimestamp(),
            $to->getTimestamp(),
        );

        /** @var DashboardSummaryData */
        return $this->cache->remember($cacheKey, now()->addSeconds(30), function () use ($projectId, $from, $to): DashboardSummaryData {
            return new DashboardSummaryData(
                eventsLast24h: $this->store->countEvents($projectId, $from, $to),
                uniqueVisitors: $this->store->uniqueVisitors($projectId, $from, $to),
                ingestP95Ms: 0,
                eventsOverTime: $this->store->eventsOverTime($projectId, $from, $to, TimeGranularity::Hour),
            );
        });
    }
}
