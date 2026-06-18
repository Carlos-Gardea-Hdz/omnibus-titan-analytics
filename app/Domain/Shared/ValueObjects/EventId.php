<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * UUIDv7 event identifier (chronologically sortable — good index locality).
 *
 * If it constructs, it is valid. Used as the per-event idempotency key end to end:
 * the queue, the loader batch and the downstream ReplacingMergeTree all dedup on it.
 */
final readonly class EventId
{
    private function __construct(public string $value)
    {
        if (! Str::isUuid($value)) {
            throw new InvalidArgumentException('EventId must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(Str::uuid7()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
