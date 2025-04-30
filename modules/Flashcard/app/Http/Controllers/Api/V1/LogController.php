<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Flashcard\app\Services\LogService;
use Symfony\Component\HttpFoundation\Response;

final class LogController extends Controller
{
    public function __construct(
        private readonly LogService $logService,
    ) {}

    /**
     * Get all logs for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $limit = $request->integer('limit', 50);

        $logs = $this->logService->getLogsForUser($userId, $limit);

        return response()->json($logs);
    }

    /**
     * Get latest activity for the authenticated user
     */
    public function latest(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $limit = $request->integer('limit', 10);

        $activities = $this->logService->getLatestActivityForUser($userId, $limit);

        return response()->json($activities);
    }
}
