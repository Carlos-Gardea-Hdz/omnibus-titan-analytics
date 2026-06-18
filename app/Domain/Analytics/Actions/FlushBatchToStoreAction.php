<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Actions;

use App\Domain\Analytics\Repositories\EventStoreRepository;

/**
 * Loader stage: idempotently flush one buffered batch to the columnar store.
 *
 * The flush MUST be idempotent on retry — a re-flushed batch double-counts
 * regardless of downstream ReplacingMergeTree dedup. The repository keys on
 * the batch idempotency key so a retried job is a no-op.
 */
final readonly class FlushBatchToStoreAction
{
    public function __construct(private EventStoreRepository $store) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function handle(string $batchIdempotencyKey, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->store->insertBatch($batchIdempotencyKey, $rows);
    }
}
