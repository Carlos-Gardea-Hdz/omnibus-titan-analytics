<?php

declare(strict_types=1);

use App\Domain\Analytics\ValueObjects\AnonymizedIp;

it('truncates the last octet of an IPv4 address', function (): void {
    expect((string) AnonymizedIp::fromRaw('203.0.113.42'))->toBe('203.0.113.0');
});

it('truncates an IPv6 address to its /48 prefix', function (): void {
    $anon = (string) AnonymizedIp::fromRaw('2001:db8:abcd:1234:5678:9abc:def0:1234');

    expect($anon)->toStartWith('2001:db8:abcd:');
    expect($anon)->not->toContain('5678');
});

it('never returns the raw IP', function (): void {
    expect((string) AnonymizedIp::fromRaw('198.51.100.77'))->not->toBe('198.51.100.77');
});

it('rejects an invalid IP', function (): void {
    AnonymizedIp::fromRaw('not-an-ip');
})->throws(InvalidArgumentException::class);
