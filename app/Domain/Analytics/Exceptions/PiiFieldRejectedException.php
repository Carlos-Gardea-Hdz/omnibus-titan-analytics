<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Exceptions;

use RuntimeException;

/**
 * Thrown (fail loud) when an inbound event carries a forbidden PII field.
 * Data minimization is non-negotiable: capture what users DO, not who they ARE.
 */
final class PiiFieldRejectedException extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self("Rejected event: forbidden PII field [{$key}] is not permitted in the analytics store.");
    }
}
