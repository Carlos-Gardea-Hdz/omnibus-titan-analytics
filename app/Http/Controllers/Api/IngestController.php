<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Analytics\Actions\IngestEventsAction;
use App\Domain\Analytics\Data\IngestBatchData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Collector endpoint. Anemic by law: validate (via DTO) -> call Action ->
 * return 202 fast. NEVER writes to ClickHouse synchronously here.
 */
final class IngestController extends Controller
{
    public function store(Request $request, IngestEventsAction $action): JsonResponse
    {
        $batch = IngestBatchData::validateAndCreate($request->all());

        $project = $request->attributes->get('project_id');
        $projectId = is_string($project) ? $project : 'unknown';
        $salt = config('analytics.session_salt');
        $dailySalt = (is_string($salt) ? $salt : '').now()->toDateString();

        $batchKey = $action->handle($batch, $projectId, $request->ip() ?? '', $dailySalt);

        return response()->json(['accepted' => true, 'batch' => $batchKey], 202);
    }
}
