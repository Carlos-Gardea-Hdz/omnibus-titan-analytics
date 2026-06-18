<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Repositories;

use App\Domain\Analytics\Enums\TimeGranularity;
use Carbon\CarbonImmutable;

/**
 * Placeholder store implementation for local/dev/test scaffolding.
 *
 * The production implementation talks to ClickHouse via a vetted L12/PHP8.5
 * driver (verify with `composer why-not` before pinning — analytics-data.md
 * "Maintainability"). Because all store access is isolated behind
 * EventStoreRepository, swapping this for the real driver is contained.
 */
final class NullEventStoreRepository implements EventStoreRepository
{
    public function insertBatch(string $batchIdempotencyKey, array $rows): void
    {
        // no-op: real implementation performs an idempotent, batched async insert.
    }

    public function countEvents(string $projectId, CarbonImmutable $from, CarbonImmutable $to): int
    {
        return 0;
    }

    public function uniqueVisitors(string $projectId, CarbonImmutable $from, CarbonImmutable $to): int
    {
        return 0;
    }

    public function eventsOverTime(
        string $projectId,
        CarbonImmutable $from,
        CarbonImmutable $to,
        TimeGranularity $granularity,
    ): array {
        return [];
    }

    public function eraseSubject(string $projectId, string $subjectHash): void
    {
        // no-op
    }
}
