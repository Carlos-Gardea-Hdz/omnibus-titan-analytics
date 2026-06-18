<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One aggregated time bucket returned to the dashboard. Marked #[TypeScript]
 * so the frontend type is generated, never hand-redeclared.
 */
#[TypeScript]
final class TimeBucketData extends Data
{
    public function __construct(
        public string $bucket,
        public int $count,
    ) {}
}
