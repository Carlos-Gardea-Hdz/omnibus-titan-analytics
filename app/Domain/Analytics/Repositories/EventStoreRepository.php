<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Repositories;

use App\Domain\Analytics\Data\TimeBucketData;
use App\Domain\Analytics\Enums\TimeGranularity;
use Carbon\CarbonImmutable;

/**
 * Abstraction over the columnar event store (ClickHouse).
 *
 * ClickHouse is ISOLATED behind this interface (analytics-data.md): Inertia
 * controllers and Actions never issue raw ClickHouse SQL. Swapping the
 * underlying driver (the rules flag the L12/PHP8.5 driver question as one to
 * verify with `composer why-not`) stays contained to the implementation.
 *
 * @param  array<int, array<string, mixed>>  $rows
 */
interface EventStoreRepository
{
    /**
     * Idempotently flush a pre-validated, anonymized batch to the store.
     * The batch carries an idempotency key; a re-flushed batch MUST NOT
     * double-count (worker-retry idempotency).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function insertBatch(string $batchIdempotencyKey, array $rows): void;

    /** Count events in a time window for a project (reads from an MV rollup). */
    public function countEvents(string $projectId, CarbonImmutable $from, CarbonImmutable $to): int;

    /** Approximate unique visitors via uniq-state merge (reads from an MV rollup). */
    public function uniqueVisitors(string $projectId, CarbonImmutable $from, CarbonImmutable $to): int;

    /**
     * Bucketed event counts over a window, read from the granularity's MV rollup.
     *
     * @return array<int, TimeBucketData>
     */
    public function eventsOverTime(
        string $projectId,
        CarbonImmutable $from,
        CarbonImmutable $to,
        TimeGranularity $granularity,
    ): array;

    /**
     * GDPR Art. 17 hard erasure: physically remove a subject's rows and confirm
     * the removal is complete (a lightweight DELETE is NOT sufficient).
     */
    public function eraseSubject(string $projectId, string $subjectHash): void;
}
