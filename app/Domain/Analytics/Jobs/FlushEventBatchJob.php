<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Jobs;

use App\Domain\Analytics\Actions\FlushBatchToStoreAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Thin queue wrapper around FlushBatchToStoreAction (laravel-advanced.md §3:
 * jobs control HOW it runs; the Action does the work). Idempotent on retry via
 * the batch idempotency key — a re-flushed batch must not double-count.
 */
final class FlushEventBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int Explicit retry budget. */
    public int $tries = 5;

    /** @var int Per-attempt timeout (seconds). */
    public int $timeout = 60;

    public int $maxExceptions = 3;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        public readonly string $batchIdempotencyKey,
        public readonly array $rows,
    ) {
        $this->onQueue('ingest');
    }

    /** @return array<int, int> Escalating backoff between retries. */
    public function backoff(): array
    {
        return [5, 15, 60, 180];
    }

    public function handle(FlushBatchToStoreAction $action): void
    {
        $action->handle($this->batchIdempotencyKey, $this->rows);
    }

    public function failed(Throwable $exception): void
    {
        // Route to a dead-letter sink + alert (never silently drop a batch).
        report($exception);
    }

    /** @return array<int, string> */
    public function tags(): array
    {
        return ['analytics', 'ingest', "batch:{$this->batchIdempotencyKey}"];
    }
}
