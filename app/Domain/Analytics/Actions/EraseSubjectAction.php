<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Actions;

use App\Domain\Analytics\Repositories\EventStoreRepository;

/**
 * GDPR Art. 17 erasure: GUARANTEED hard delete of a subject's rows.
 *
 * Delegates to the store's heavy-mutation erasure (not a lightweight DELETE)
 * and is the single entry point for a Subject Erasure Request. Callers should
 * record an immutable audit-log entry of the erasure in PostgreSQL.
 */
final readonly class EraseSubjectAction
{
    public function __construct(private EventStoreRepository $store) {}

    public function handle(string $projectId, string $subjectHash): void
    {
        $this->store->eraseSubject($projectId, $subjectHash);
    }
}
