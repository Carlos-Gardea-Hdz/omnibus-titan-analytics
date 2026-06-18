# Architecture — Titan Analytics

Decisions distilled from the OMNIBUS vault module `~/.claude/.agent-rules/analytics-data.md`.
This document is the map; the rules are the law.

## 1. Split storage tiers

- **PostgreSQL 18 (OLTP)** — accounts, projects, write-only API keys (hashed), dashboard
  configs, consent records, erasure audit log. Eloquent + reversible migrations.
- **ClickHouse 26.3 LTS (event store)** — the high-volume `events` table is the single
  source of truth; all dashboard reads come from **materialized-view rollups**, never raw
  scans. High-volume events are **never** stored in PostgreSQL.

ClickHouse is **isolated behind `EventStoreRepository`** (`app/Domain/Analytics/Repositories`).
Controllers and Actions never issue raw ClickHouse SQL. A `NullEventStoreRepository` keeps
dev/test green until the production driver is bound — see §7.

## 2. Three-stage pipeline

```
client → [ Collector ] → [ Buffer/Queue ] → [ Loader ] → ClickHouse
          IngestController   Valkey (ingest    FlushEventBatchJob
          + IngestEventsAction  queue)         + FlushBatchToStoreAction
```

- **Collector** (`IngestController`, ≤15 lines): validate via DTO → reject PII → anonymize
  IP → enqueue → `202 Accepted`. **Never** writes to ClickHouse synchronously.
- **Buffer**: Valkey-backed queue (`ingest` queue), separate from cache/session DBs.
- **Loader**: a thin queue Job delegates to `FlushBatchToStoreAction`, which performs an
  **idempotent batched async insert** (`async_insert=1`, `wait_for_async_insert=1` — never
  fire-and-forget). Idempotency key = batch UUIDv7; downstream dedup via `ReplacingMergeTree`.

## 3. DDD Lite layout

```
app/Domain/Analytics/
├── Actions/        IngestEventsAction · FlushBatchToStoreAction
│                   AggregateDashboardAction · EraseSubjectAction
├── Data/           IngestEventData · IngestBatchData (edge validation)
│                   DashboardSummaryData · TimeBucketData  (#[TypeScript])
├── Enums/          EventType · TimeGranularity (backed enums)
├── ValueObjects/   AnonymizedIp (truncates/never stores raw IP)
├── Jobs/           FlushEventBatchJob (thin wrapper → Action)
├── Repositories/   EventStoreRepository (interface) · NullEventStoreRepository
└── Exceptions/     PiiFieldRejectedException
app/Domain/Shared/ValueObjects/   EventId (UUIDv7, idempotency key)
```

Controllers are anemic (DTO in → Action → Response). Actions are the public API of the
domain; multi-table writes wrap `DB::transaction()`. All PHP files `declare(strict_types=1)`;
classes `final`, props `readonly`, money in integer cents, UUIDs v7 only.

## 4. Ingestion hardening (highest-risk path)

- Every event validated against `IngestEventData` before it touches the queue.
- `EnforceIngestLimits` middleware caps request body size (512 KB) and JSON depth (8) and
  the batch DTO caps events/batch (1000) — array-bomb / JSON-bomb DoS guard.
- Per-project write-only API keys (hashed in PG), rate-limited per key (`throttle:ingest`).
- ClickHouse credentials are **never** exposed to clients.

## 5. Privacy & compliance

- **No raw IP** ever stored — `AnonymizedIp` truncates IPv4 to /24 and IPv6 to /48 at the edge.
- Cookieless **ephemeral session IDs**: `sha256(daily_salt | truncated_ip)`; the salt rotates
  daily so the ID cannot track across days.
- PII fields (`email`, `name`, `phone`, `ip`, `curp`, `rfc`, …) are rejected by design.
- Retention capped at **13 months** via ClickHouse `TTL ... DELETE` (`ttl_only_drop_parts=1`).
- GDPR Art. 17: `EraseSubjectAction` performs a **guaranteed hard delete** (heavy mutation /
  drop partition), not a logical hide; every erasure is audit-logged in PostgreSQL.

## 6. Dashboards (UI/UX & accessibility)

- React 19 + Inertia + Tailwind v4. Props are typed `#[TypeScript]` DTOs generated to
  `resources/js/types/generated.d.ts` — the single source of truth; the frontend never
  redeclares server shapes.
- Reads go through `AggregateDashboardAction`: MV rollups only, bounded time range, result
  cached in Valkey with a short TTL. No raw rows or SQL reach the browser.
- Every chart (`TimeSeriesChart`) ships a visually-hidden **data-table fallback**, ARIA
  roles, and a **theme-aware palette**. Dark/light is mandatory; color is never the only
  encoding. Skip link, focus rings, and `prefers-reduced-motion` are honored.

## 7. ClickHouse schema decisions (to implement)

- `events`: `MergeTree`, `ORDER BY (project_id, event_type, toStartOfHour(timestamp))`,
  `PARTITION BY toYYYYMM(timestamp)` (lifecycle/TTL only — never for performance).
- Right-sized types: `LowCardinality(String)` for bounded sets, `DateTime64`, no `Nullable`;
  native `JSON` (with path hints) for properties; `Delta`/`DoubleDelta` + `ZSTD` codecs.
- Rollups via `AggregatingMergeTree` MVs using the `-State`/`-Merge` pattern
  (`uniqState`/`quantileState`); dedup via `ReplacingMergeTree` (eventual, same-partition).
- **Driver**: pick a ClickHouse Laravel driver that explicitly supports L12/PHP 8.5 and
  verify with `composer why-not` before pinning; bind it to `EventStoreRepository` in
  `AppServiceProvider`. The repository boundary keeps a driver swap contained.

## 8. Security baseline

- `SecurityHeaders` middleware: nonce-based CSP (report-only first), HSTS (staged),
  `nosniff`, `Referrer-Policy`, COOP/CORP, `Permissions-Policy`.
- Server-side sessions on Valkey; secure cookies (no insecure default); CSRF on for Inertia.
- Supply chain: committed lockfiles, `pnpm` `minimumReleaseAge`, install-script allowlist;
  audits (`composer audit` / `pnpm audit`) gate CI. React pinned to a patched 19.2 line
  (CVE-2025-55182 floor).

## 9. Quality gates

Pint → PHPStan/Larastan **level 9** → Pest 4 (incl. architecture tests) → `tsc` → ESLint →
`vite build`. Run before every commit. `php artisan typescript:transform` keeps TS types in sync.
