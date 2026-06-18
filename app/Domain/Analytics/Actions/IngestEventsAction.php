<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Actions;

use App\Domain\Analytics\Data\IngestBatchData;
use App\Domain\Analytics\Data\IngestEventData;
use App\Domain\Analytics\Exceptions\PiiFieldRejectedException;
use App\Domain\Analytics\Jobs\FlushEventBatchJob;
use App\Domain\Analytics\ValueObjects\AnonymizedIp;
use App\Domain\Shared\ValueObjects\EventId;
use Carbon\CarbonImmutable;

/**
 * Collector stage: validate -> reject PII -> anonymize -> enrich -> enqueue.
 *
 * NEVER writes to ClickHouse from the request path. This Action prepares
 * normalized rows and pushes them to the durable queue; a worker
 * (FlushEventBatchJob) batches them to the store. The HTTP path returns fast.
 */
final readonly class IngestEventsAction
{
    /**
     * @return string The batch idempotency key (used for end-to-end dedup).
     */
    public function handle(IngestBatchData $batch, string $projectId, string $rawIp, string $dailySalt): string
    {
        $anonymizedIp = AnonymizedIp::fromRaw($rawIp);
        $sessionId = $this->ephemeralSessionId($rawIp, $dailySalt);
        $batchKey = EventId::generate()->value;

        $rows = [];

        foreach ($batch->events as $event) {
            $this->assertNoPii($event);

            $rows[] = [
                'event_id' => EventId::generate()->value,
                'project_id' => $projectId,
                'event_type' => $event->type->value,
                'url' => $event->url,
                'referrer' => $event->referrer ?? '',
                'properties' => $event->properties,
                'anonymized_ip' => (string) $anonymizedIp,
                'session_id' => $sessionId,
                'occurred_at' => ($event->occurredAt ?? CarbonImmutable::now())->toIso8601String(),
            ];
        }

        // Enqueue only — never block the ingest HTTP response on ClickHouse.
        FlushEventBatchJob::dispatch($batchKey, $rows);

        return $batchKey;
    }

    /**
     * Cookieless ephemeral session id: hash of (truncated IP) with a rotating
     * daily salt. Never a persistent client cookie; the salt rotates daily so
     * the id cannot be used as a cross-day tracker.
     */
    private function ephemeralSessionId(string $rawIp, string $dailySalt): string
    {
        return hash('sha256', $dailySalt.'|'.AnonymizedIp::fromRaw($rawIp));
    }

    private function assertNoPii(IngestEventData $event): void
    {
        $forbidden = IngestEventData::forbiddenPiiKeys();

        foreach (array_keys($event->properties) as $key) {
            if (in_array(strtolower((string) $key), $forbidden, true)) {
                throw PiiFieldRejectedException::forKey((string) $key);
            }
        }
    }
}
