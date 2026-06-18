<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * A bounded batch of events. Caps the events-per-request count (array-bomb DoS
 * guard) — the request body size is capped separately by middleware.
 */
final class IngestBatchData extends Data
{
    /**
     * @param  DataCollection<int, IngestEventData>  $events
     */
    public function __construct(
        #[Min(1), Max(1000)]
        public DataCollection $events,
    ) {}
}
