<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;

interface LogRepositoryInterface
{
    /**
     * Get logs for a user.
     */
    public function getLogsForUser(int $userId, int $limit = 50): array;

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
    public function logStudySessionStart(int $userId, StudySession $studySession): Log;

    /**
     * Log study session end.
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log;

    /**
     * Log practice reset.
     */
    public function logPracticeReset(int $userId): Log;
}
