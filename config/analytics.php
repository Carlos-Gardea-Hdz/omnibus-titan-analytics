<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Rotating session salt
    |--------------------------------------------------------------------------
    | Base secret combined with the current date to derive cookieless, daily
    | ephemeral session IDs. NEVER a persistent client cookie. Set via env.
    */
    'session_salt' => env('ANALYTICS_SESSION_SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | Ingestion edge limits
    |--------------------------------------------------------------------------
    */
    'ingest' => [
        'max_events_per_batch' => (int) env('ANALYTICS_MAX_BATCH', 1000),
        'max_body_bytes' => (int) env('ANALYTICS_MAX_BODY_BYTES', 512 * 1024),
        'max_json_depth' => (int) env('ANALYTICS_MAX_JSON_DEPTH', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention (privacy)
    |--------------------------------------------------------------------------
    | Capped to the consent-exemption ceiling. Enforced as a ClickHouse TTL in
    | the table DDL: `TTL timestamp + INTERVAL {months} MONTH DELETE`.
    */
    'retention_months' => (int) env('ANALYTICS_RETENTION_MONTHS', 13),

    /*
    |--------------------------------------------------------------------------
    | ClickHouse connection (least privilege)
    |--------------------------------------------------------------------------
    | Separate INSERT-only (loader) and read-only (dashboard) users. TLS enforced.
    | Never reuse a superuser. See config/database.php 'clickhouse' connection.
    */
    'clickhouse' => [
        'database' => env('CLICKHOUSE_DATABASE', 'titan_analytics'),
        'async_insert' => true,
        'wait_for_async_insert' => true, // never fire-and-forget
    ],

];
