# NEXT_STEPS — omnibus-titan-analytics

Foundation scaffolded 2026-06-18 (commit `500e4ea`, 104 files). This file lists what was verified and what remains; build the domain out via `/sdd`.

## Verified at scaffold time

All gates run in this session and PASSED: composer install/update resolved cleanly (Laravel 12, Pest 4, Larastan 3, Spatie Data v4, Inertia-laravel 2). Pest: 14 passed / 26 assertions. PHPStan/Larastan level 9: No errors (fixed 7 real type-safety issues found — AnonymizedIp false-handling, mixed-to-string casts in 3 files). Pint --test: PASS, 52 files. Frontend: pnpm install OK; tsc --noEmit clean; ESLint --max-warnings=0 clean; vite production build succeeded (1401 modules, code-split per page). php artisan typescript:transform generated correct TS types. composer audit + pnpm audit: no vulnerabilities. NOT verified: full runtime against live PostgreSQL/Valkey/ClickHouse (no live services; tests use sqlite :memory: + sync queue/array cache + Null event store) and SSR server boot.

## Known gaps / issues

- ClickHouse access is stubbed by NullEventStoreRepository — no real driver is bound yet. The vault rule explicitly flags that the L12/PHP 8.5 ClickHouse driver must be chosen and verified with `composer why-not` before pinning; this was left as a deliberate next step behind the repository boundary.
- No ClickHouse schema migrations exist yet (events MergeTree table, TTL, ReplacingMergeTree dedup, AggregatingMergeTree MV rollups) — only the target design is documented in ARCHITECTURE.md §7.
- No authentication scaffolding (Breeze/Sanctum API-key middleware) installed; the /dashboard route and ingest API-key auth are placeholders. Sanctum package is required but its guard/middleware are not wired.
- Runtime was not exercised against live PostgreSQL 18 / Valkey 8 / ClickHouse 26.3 — those services are not available in this environment.
- Recharts 2.15.4 is marked deprecated by its registry entry (v3 exists); chosen for current Tailwind/React-19 compatibility but should be re-evaluated.
- composer.json php constraint set to ^8.4 (container has 8.5.7); confirm production targets PHP 8.5 explicitly if desired.

## Next steps

- [ ] Select and bind a vetted Laravel-12/PHP-8.5 ClickHouse driver to EventStoreRepository in AppServiceProvider (verify with `composer why-not`); implement the real repository (idempotent async_insert batch, MV-rollup reads, hard-erasure mutation).
- [ ] Write ClickHouse schema migrations: events MergeTree (ORDER BY project_id,event_type,toStartOfHour(ts); PARTITION BY toYYYYMM; 13-month TTL), ReplacingMergeTree dedup, AggregatingMergeTree MV rollups (minute→hour→day→month) using -State/-Merge.
- [ ] Install auth: Sanctum personal-access / per-project write-only API keys (hashed in PG) + an auth.apikey middleware on the ingest route; add Breeze (React+TS) for dashboard login and gate /dashboard.
- [ ] Add a real PostgreSQL OLTP schema (projects, api_keys, consent_records, erasure_audit) with reversible migrations + factories/seeders (fictional demo data only).
- [ ] Add integration tests against a real ClickHouse Docker container (FINAL/-Merge/dedup semantics, MV correctness, worker-retry no-double-count, physical-erasure assertion).
- [ ] Stand up docker-compose / Sail with PostgreSQL 18, Valkey 8, ClickHouse 26.3 and Horizon for the ingest queue; configure Octane+FrankenPHP for serving.
- [ ] Flip CSP from report-only to enforcing after collecting violations; add Reporting-Endpoints; wire the CSP nonce into the Blade/Vite script tags.
- [ ] Set up CI (GitHub Actions, SHA-pinned) running Pint, PHPStan L9, Pest, composer audit, pnpm audit, tsc, eslint, build.
