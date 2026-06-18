<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Edge hardening for the ingest path (analytics-data.md "Ingestion"):
 * reject oversized request bodies and deeply-nested JSON before parsing —
 * JSON/array-bomb DoS and JSON-column path-explosion guard.
 */
final class EnforceIngestLimits
{
    private const MAX_BODY_BYTES = 512 * 1024; // 512 KB

    private const MAX_JSON_DEPTH = 8;

    public function handle(Request $request, Closure $next): Response
    {
        $length = (int) $request->header('Content-Length', '0');
        if ($length > self::MAX_BODY_BYTES) {
            return response()->json(['error' => 'Payload too large'], 413);
        }

        $raw = $request->getContent();
        if (strlen($raw) > self::MAX_BODY_BYTES) {
            return response()->json(['error' => 'Payload too large'], 413);
        }

        // json_decode with a depth cap fails closed on overly-nested payloads.
        if ($raw !== '' && json_decode($raw, true, self::MAX_JSON_DEPTH) === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Malformed or too deeply nested JSON'], 422);
        }

        return $next($request);
    }
}
