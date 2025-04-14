<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Flashcard\app\Services\StudySessionService;
use Symfony\Component\HttpFoundation\Response;

final class StudySessionController extends Controller
{
    public function __construct(
        private readonly StudySessionService $studySessionService,
    ) {}

    /**
     * Start a new study session
     */
    public function start(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $session = $this->studySessionService->startSession($userId);

        return response()->json($session, Response::HTTP_CREATED);
    }

    /**
     * End an active study session
     */
    public function end(Request $request, int $studySession): JsonResponse
    {
        $userId = $request->user()->id;

        $result = $this->studySessionService->endSession($userId, $studySession);

        if (! $result) {
            return response()->json([
                'message' => 'Study session not found or already ended',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Study session ended successfully',
        ]);
    }

    /**
     * Get flashcards for practice
     */
    public function getFlashcardsForPractice(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $flashcards = $this->studySessionService->getFlashcardsForPractice($userId);

        return response()->json($flashcards);
    }

    /**
     * Record practice result
     */
    public function recordPracticeResult(Request $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()->id;
        $isCorrect = (bool) $request->input('is_correct', false);

        $result = $this->studySessionService->recordPracticeResult($userId, $flashcard, $isCorrect);

        if (! $result) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Practice result recorded successfully',
        ]);
    }

    /**
     * Reset practice progress
     */
    public function resetPractice(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $this->studySessionService->resetPracticeProgress($userId);

        return response()->json([
            'message' => 'Practice progress reset successfully',
        ]);
    }
}
