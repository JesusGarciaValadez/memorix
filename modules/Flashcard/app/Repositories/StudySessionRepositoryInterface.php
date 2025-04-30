<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use Modules\Flashcard\app\Models\StudySession;

interface StudySessionRepositoryInterface
{
    /**
     * Start a new study session.
     */
    public function startSession(int $userId): StudySession;

    /**
     * End a study session.
     */
    public function endSession(StudySession $studySession): bool;

    /**
     * Find a study session for a user.
     */
    public function findForUser(int $sessionId, int $userId): ?StudySession;

    /**
     * Get active session for a user.
     */
    public function getActiveSessionForUser(int $userId): ?StudySession;

    /**
     * Get flashcards for practice.
     *
     * @return array<int, array{id: int, question: string, answer: string}>
     */
    public function getFlashcardsForPractice(int $userId): array;

    /**
     * Record practice result.
     */
    public function recordPracticeResult(int $userId, int $flashcardId, bool $isCorrect): bool;

    /**
     * Reset practice progress.
     */
    public function resetPracticeProgress(int $userId): bool;

    /**
     * Delete all study sessions for a user.
     */
    public function deleteAllForUser(int $userId): bool;

    /**
     * Get the latest practice result for a flashcard.
     *
     * @return array{id: int, flashcard_id: int, study_session_id: int, is_correct: bool, created_at: string}|null
     */
    public function getLatestResultForFlashcard(int $flashcardId): ?array;
}
