<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;

interface LogServiceInterface
{
    /**
     * Get logs for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForUser(int $userId, int $limit = 50): array;

    /**
     * Get latest activity for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestActivityForUser(int $userId, int $limit = 10): array;

    /**
     * Log user login.
     */
    public function logUserLogin(int $userId): Log;

    /**
     * Log flashcard creation.
     */
    public function logFlashcardCreation(int $userId, Flashcard $flashcard): Log;

    /**
     * Log flashcard update.
     */
    public function logFlashcardUpdate(int $userId, Flashcard $flashcard): Log;

    /**
     * Log flashcard deletion.
     */
    public function logFlashcardDeletion(int $userId, Flashcard $flashcard): Log;

    /**
     * Log flashcard list view.
     */
    public function logFlashcardList(int $userId): Log;

    /**
     * Log flashcard restoration.
     */
    public function logFlashcardRestoration(int $userId, Flashcard $flashcard): Log;

    /**
     * Log flashcard practice.
     */
    public function logFlashcardPractice(int $userId, Flashcard $flashcard, bool $isCorrect): Log;

    /**
     * Log study session start.
     */
    public function logStudySessionStart(int $userId, StudySession $studyStudySession): Log;

    /**
     * Log study session end.
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log;

    /**
     * Log statistics view.
     */
    public function logStatisticsView(int $userId): Log;

    /**
     * Log practice reset.
     */
    public function logPracticeReset(int $userId): Log;

    /**
     * Log user exit.
     */
    public function logUserExit(int $userId): Log;

    /**
     * Log restoration of all flashcards.
     */
    public function logAllFlashcardsRestore(int $userId): Log;

    /**
     * Log permanent deletion of all flashcards.
     */
    public function logAllFlashcardsPermanentDelete(int $userId): Log;

    /**
     * Log import of flashcards from file.
     */
    public function logFlashcardImport(int $userId, int $importCount): Log;

    /**
     * Log permanent deletion of a single flashcard.
     */
    public function logFlashcardForceDelete(int $userId, int $flashcardId, string $flashcardQuestion): Log;
}
