<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Flashcard\app\Services\StatisticServiceInterface;

final class StatisticController extends Controller
{
    public function __construct(
        private readonly StatisticServiceInterface $statisticService,
    ) {}

    /**
     * Get all statistics for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $statistics = $this->statisticService->getStatisticsForUser($userId);

        return response()->json($statistics);
    }

    /**
     * Get success rate for the authenticated user
     */
    public function successRate(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $successRate = $this->statisticService->getPracticeSuccessRate($userId);

        return response()->json([
            'success_rate' => $successRate,
        ]);
    }

    /**
     * Get average study session duration for the authenticated user
     */
    public function averageDuration(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $averageDuration = $this->statisticService->getAverageStudySessionDuration($userId);

        return response()->json([
            'average_duration' => $averageDuration,
        ]);
    }

    /**
     * Get total study time for the authenticated user
     */
    public function totalTime(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totalTime = $this->statisticService->getTotalStudyTime($userId);

        return response()->json([
            'total_time' => $totalTime,
        ]);
    }
}
