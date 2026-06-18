<?php

declare(strict_types=1);

namespace App\Domain\Analytics\ValueObjects;

use InvalidArgumentException;

/**
 * Privacy-by-design IP handling (analytics-data.md "Privacy & Compliance").
 *
 * NEVER stores a raw IP. IPv4 is truncated to its /24 (last octet zeroed);
 * IPv6 is truncated to its /48 prefix. This happens at the edge BEFORE the
 * event enters the queue — raw IPs are personal data under GDPR and must not
 * reach the event store.
 */
final readonly class AnonymizedIp
{
    private function __construct(public string $value) {}

    public static function fromRaw(string $rawIp): self
    {
        $rawIp = trim($rawIp);

        if (filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $octets = explode('.', $rawIp);
            $octets[3] = '0';

            return new self(implode('.', $octets));
        }

        if (filter_var($rawIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($rawIp);
            if ($packed === false) {
                throw new InvalidArgumentException('Cannot anonymize an invalid IPv6 address.');
            }

            // Keep the first 48 bits (6 bytes); zero the rest.
            $masked = substr($packed, 0, 6).str_repeat("\0", 10);
            $printable = inet_ntop($masked);
            if ($printable === false) {
                throw new InvalidArgumentException('Cannot render the anonymized IPv6 address.');
            }

            return new self($printable);
        }

        throw new InvalidArgumentException('Cannot anonymize an invalid IP address.');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
