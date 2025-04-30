<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Models\StudySession;

interface StudySessionServiceInterface
{
    /**
     * Start a new study session for a user.
     */
    public function startSession(int $userId): StudySession;

    /**
     * End a study session.
     */
    public function endSession(int $userId, int $sessionId): bool;

    /**
     * Get flashcards for a practice session.
     *
     * @param  int  $userId  The ID of the user.
     * @return array<int, array{id: int, question: string, answer: string}> An array of flashcards for practice.
     */
    public function getFlashcardsForPractice(int $userId): array;

    /**
     * Record a practice result.
     */
    public function recordPracticeResult(int $userId, int $flashcardId, bool $isCorrect): bool;

    /**
     * Reset practice progress.
     */
    public function resetPracticeProgress(int $userId): bool;
}
