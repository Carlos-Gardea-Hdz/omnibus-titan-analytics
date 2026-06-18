<?php

declare(strict_types=1);

use App\Http\Controllers\Api\IngestController;
use App\Http\Middleware\EnforceIngestLimits;
use Illuminate\Support\Facades\Route;

/*
 * Ingestion endpoint. Per-project write-only API keys (hashed at rest in PG)
 * authenticate the caller and are rate-limited per key. The collector
 * validates -> enqueues -> returns 202 (never writes to ClickHouse inline).
 *
 * The `auth.apikey` middleware is a documented next step (see ARCHITECTURE.md);
 * EnforceIngestLimits caps body size / JSON depth at the edge today.
 */
Route::middleware([EnforceIngestLimits::class, 'throttle:ingest'])
    ->post('/v1/events', [IngestController::class, 'store'])
    ->name('api.events.ingest');
