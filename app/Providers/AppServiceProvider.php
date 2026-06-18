<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Analytics\Repositories\EventStoreRepository;
use App\Domain\Analytics\Repositories\NullEventStoreRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ClickHouse access is isolated behind an interface. Bind the real
        // driver-backed implementation here once the vetted L12/PHP8.5 driver
        // is selected; the Null implementation keeps dev/test green meanwhile.
        $this->app->bind(EventStoreRepository::class, NullEventStoreRepository::class);
    }

    public function boot(): void
    {
        // Fail loud in non-production: catch lazy loads, missing attributes, etc.
        \Illuminate\Database\Eloquent\Model::shouldBeStrict(! $this->app->isProduction());

        // Per-API-key ingest rate limit (keyed by the resolved project, IP fallback).
        RateLimiter::for('ingest', static function (Request $request): Limit {
            $project = $request->attributes->get('project_id');
            $key = is_string($project) ? $project : ($request->ip() ?? 'unknown');

            return Limit::perMinute(600)->by($key);
        });
    }
}
