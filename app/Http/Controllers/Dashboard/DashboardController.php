<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Analytics\Actions\AggregateDashboardAction;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard read endpoint. Anemic: resolve range -> call Action -> render the
 * typed DTO as Inertia props. No ClickHouse SQL or raw rows reach the browser.
 */
final class DashboardController extends Controller
{
    public function show(Request $request, AggregateDashboardAction $action): Response
    {
        $identifier = $request->user()?->getAuthIdentifier();
        $projectId = is_scalar($identifier) ? (string) $identifier : 'unknown';
        $to = CarbonImmutable::now();
        $from = $to->subDay();

        $summary = $action->handle($projectId, $from, $to);

        return Inertia::render('Dashboard', $summary->toArray());
    }
}
