# Titan Analytics

Privacy-first analytics platform: **event ingestion API**, **ClickHouse** columnar
event store, and **segmented React/Inertia dashboards**. Part of the OMNIBUS program
(`titan.carlosgardea.com`, chapter 4.7).

## What it is

- **Ingest API** — a fast collector endpoint that validates an event batch, strips/rejects
  PII, anonymizes the IP at the edge, enqueues to Valkey, and returns `202 Accepted`. It
  **never** writes to ClickHouse on the request path.
- **Loader** — a queue worker batches events and flushes them idempotently to ClickHouse.
- **Dashboards** — React 19 + Inertia pages that read **pre-aggregated rollups** through a
  typed query layer. No raw event rows or ClickHouse SQL ever reach the browser.
- **Privacy by design** — cookieless ephemeral session IDs (rotating daily salt), truncated
  IPs, a 13-month retention ceiling, and a guaranteed GDPR Art. 17 hard-erasure Action.

## Stack

| Layer        | Choice                                                            |
|--------------|-------------------------------------------------------------------|
| Backend      | PHP 8.5 · Laravel 12                                              |
| OLTP store   | PostgreSQL 18 (accounts, projects, API keys, consent)            |
| Event store  | ClickHouse 26.3 LTS (columnar) — isolated behind a repository    |
| Cache/queue  | Valkey 8 (Redis protocol)                                        |
| Frontend     | React 19 · Inertia 2 · TypeScript 5.9 · Tailwind v4 · Recharts   |
| Validation   | Spatie Laravel Data v4 (DTOs = single source of truth + TS types)|
| Tests        | Pest 4 · PHPStan/Larastan level 9 · Pint · ESLint · tsc          |

Architecture follows **DDD Lite + Action pattern**; see [`ARCHITECTURE.md`](ARCHITECTURE.md).

## Requirements

- Docker (PHP/Composer run in containers — no host PHP needed)
- Node 24 + pnpm (frontend)
- A reachable PostgreSQL 18, Valkey 8, and ClickHouse 26.3 for full runtime

## Getting started

```bash
# 1. Backend dependencies (containerized Composer)
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 install

# 2. Environment
cp .env.example .env
docker run --rm -v "$PWD":/app -w /app composer:2 php artisan key:generate

# 3. Frontend dependencies + build
pnpm install
pnpm run build            # or: pnpm run dev  (Vite dev server, localhost only)

# 4. Generate TypeScript types from PHP DTOs (single source of truth)
docker run --rm -v "$PWD":/app -w /app composer:2 php artisan typescript:transform

# 5. Migrate the OLTP store (ClickHouse schema is managed separately)
docker run --rm -v "$PWD":/app -w /app composer:2 php artisan migrate
```

Serve locally with `composer dev` (server + queue worker + logs + Vite), or use Laravel Sail.

## Quality gates (run before every commit)

```bash
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 vendor/bin/pest               # tests
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 vendor/bin/phpstan analyse    # level 9
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 vendor/bin/pint               # format
pnpm run types && pnpm run lint                                                                        # tsc + eslint
```

## Accessibility & i18n

- **Dark/light mode** via the `.dark` class on `<html>` (no-flash bootstrap in the Blade root).
- Every chart ships a **data-table text alternative**, ARIA roles, and a theme-aware palette;
  color is never the only encoding.
- All user-facing copy is **bilingual ES/EN** via the language hook (`resources/js/lib/i18n.tsx`).
- Keyboard-first: visible focus rings, a skip-to-content link, `prefers-reduced-motion` honored.

## License

MIT.
