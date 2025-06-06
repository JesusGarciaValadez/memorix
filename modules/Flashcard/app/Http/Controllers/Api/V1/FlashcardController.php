<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Flashcard\app\Services\FlashcardServiceInterface;
use Symfony\Component\HttpFoundation\Response;

final class FlashcardController extends Controller
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService,
    ) {}

    /**
     * Get all flashcards for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $perPage = $request->integer('per_page', 15);

        $flashcards = $this->flashcardService->getAllForUser($userId, $perPage);

        return response()->json($flashcards);
    }

    /**
     * Show a specific flashcard
     */
    public function show(Request $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $flashcardModel = $this->flashcardService->findForUser($userId, $flashcard);

        if (! $flashcardModel instanceof \Modules\Flashcard\app\Models\Flashcard) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($flashcardModel);
    }

    /**
     * Create a new flashcard
     */
    public function store(FormRequest $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $data = $request->validated();

        $flashcard = $this->flashcardService->create($userId, $data);

        return response()->json($flashcard, Response::HTTP_CREATED);
    }

    /**
     * Update a flashcard
     */
    public function update(FormRequest $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $data = $request->validated();

        $result = $this->flashcardService->update($userId, $flashcard, $data);

        if (! $result) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Flashcard updated successfully',
        ]);
    }

    /**
     * Get all trashed flashcards for the authenticated user
     */
    public function trash(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }
        $perPage = $request->integer('per_page', 15);

        $flashcards = $this->flashcardService->getDeletedForUser($userId, $perPage);

        return response()->json($flashcards);
    }

    /**
     * Delete a flashcard (soft delete)
     */
    public function destroy(Request $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $result = $this->flashcardService->delete($userId, $flashcard);

        if (! $result) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Flashcard deleted successfully',
        ]);
    }

    /**
     * Restore a deleted flashcard
     */
    public function restore(Request $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $result = $this->flashcardService->restore($userId, $flashcard);

        if (! $result) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Flashcard restored successfully',
        ]);
    }

    /**
     * Permanently delete a flashcard
     */
    public function forceDelete(Request $request, int $flashcard): JsonResponse
    {
        $userId = $request->user()?->id;
        if ($userId === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $result = $this->flashcardService->forceDelete($userId, $flashcard);

        if (! $result) {
            return response()->json([
                'message' => 'Flashcard not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Flashcard permanently deleted',
        ]);
    }
}
