<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use Modules\Flashcard\app\Models\PracticeResult;

interface PracticeResultRepositoryInterface
{
    /**
     * Create a new practice result.
     */
    public function create(int $userId, int $flashcardId, int $studySessionId, bool $isCorrect): bool;

    /**
     * Get practice results for a user.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, PracticeResult>
     */
    public function getForUser(int $userId);

    /**
     * Get practice results for a flashcard.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, PracticeResult>
     */
    public function getForFlashcard(int $flashcardId);

    /**
     * Get practice results for a study session.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, PracticeResult>
     */
    public function getForStudySession(int $studySessionId);

    /**
     * Delete all practice results for a user.
     */
    public function deleteForUser(int $userId): bool;

    /**
     * Check if a flashcard has been practiced in the last N days.
     */
    public function hasBeenPracticedRecently(int $flashcardId, int $days = 7): bool;

    /**
     * Get recently incorrect flashcards for a user.
     *
     * @return array<int, array{id: int, question: string, answer: string}>
     */
    public function getRecentlyIncorrectFlashcards(int $userId, int $days = 7, int $limit = 10): array;
}
