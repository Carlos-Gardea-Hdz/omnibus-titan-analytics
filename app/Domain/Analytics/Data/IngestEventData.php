<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Data;

use App\Domain\Analytics\Enums\EventType;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Data;

/**
 * Edge validation contract for a single inbound event.
 *
 * Every event MUST pass through this DTO before it touches the queue
 * (analytics-data.md "Ingestion"). Malformed payloads are rejected at the edge;
 * bad rows never reach ClickHouse. PII fields are forbidden by design — see
 * RejectsPiiFields, which runs as a `rules`-level guard.
 */
final class IngestEventData extends Data
{
    /**
     * @param  array<string, scalar|null>  $properties
     */
    public function __construct(
        public EventType $type,

        #[Max(2048)]
        public string $url,

        #[Nullable, Max(2048)]
        public ?string $referrer,

        // Semi-structured event properties. Bounded by max key count / nesting
        // depth at the controller edge (array-bomb DoS guard). Stored in the
        // ClickHouse native JSON column with path hints, never as raw String.
        // Optional: an empty `[]` is valid (an event need not carry properties).
        #[Sometimes, Present, ArrayType, Max(64)]
        public array $properties = [],

        public ?CarbonImmutable $occurredAt = null,
    ) {}

    /**
     * Forbidden top-level PII keys. A test MUST fail if any are accepted.
     *
     * @return list<string>
     */
    public static function forbiddenPiiKeys(): array
    {
        return ['email', 'name', 'full_name', 'phone', 'ip', 'ip_address', 'ssn', 'curp', 'rfc'];
    }
}
