<?php

declare(strict_types=1);

use App\Domain\Analytics\Actions\IngestEventsAction;
use App\Domain\Analytics\Data\IngestBatchData;
use App\Domain\Analytics\Data\IngestEventData;
use App\Domain\Analytics\Enums\EventType;
use App\Domain\Analytics\Exceptions\PiiFieldRejectedException;
use App\Domain\Analytics\Jobs\FlushEventBatchJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

function makeBatch(array $properties = []): IngestBatchData
{
    return IngestBatchData::from([
        'events' => [
            new IngestEventData(
                type: EventType::PageView,
                url: 'https://example.com/',
                referrer: null,
                properties: $properties,
            ),
        ],
    ]);
}

it('enqueues a flush job instead of writing synchronously', function (): void {
    $action = app(IngestEventsAction::class);

    $action->handle(makeBatch(), 'project-1', '203.0.113.10', 'salt');

    Queue::assertPushed(FlushEventBatchJob::class);
});

it('rejects an event carrying a forbidden PII field', function (): void {
    $action = app(IngestEventsAction::class);

    $action->handle(makeBatch(['email' => 'leak@example.com']), 'project-1', '203.0.113.10', 'salt');
})->throws(PiiFieldRejectedException::class);

it('never persists a raw IP in the enqueued rows', function (): void {
    $action = app(IngestEventsAction::class);

    $action->handle(makeBatch(), 'project-1', '203.0.113.10', 'salt');

    Queue::assertPushed(FlushEventBatchJob::class, function (FlushEventBatchJob $job): bool {
        foreach ($job->rows as $row) {
            expect($row['anonymized_ip'])->toBe('203.0.113.0');
            expect($row)->not->toHaveKey('ip');
        }

        return true;
    });
});
