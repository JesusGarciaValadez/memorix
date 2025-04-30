<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use JsonException;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final readonly class LogService implements LogServiceInterface
{
    public function __construct(
        private LogRepositoryInterface $logRepository,
    ) {}

    /**
     * Get logs for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return $this->logRepository->getLogsForUser($userId, $limit);
    }

    /**
     * Get latest activity for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestActivityForUser(int $userId, int $limit = 10): array
    {
        return $this->logRepository->getLogsForUser($userId, $limit);
    }

    /**
     * Log user login.
     */
    public function logUserLogin(int $userId): Log
    {
        return $this->logRepository->logUserLogin($userId);
    }

    /**
     * Create a log entry
     *
     * @throws JsonException
     */
    public function createLog(int $userId, Flashcard $flashcard): Log
    {
        return $this->logRepository->logFlashcardCreation($userId, $flashcard);
    }

    /**
     * Log a flashcard creation event
     *
     * @throws JsonException
     */
    public function logFlashcardCreation(int $userId, Flashcard $flashcard): Log
    {
        return $this->logRepository->logFlashcardCreation($userId, $flashcard);
    }

    /**
     * Log a flashcard update event
     *
     * @throws JsonException
     */
    public function logFlashcardUpdate(int $userId, Flashcard $flashcard): Log
    {
        return $this->logRepository->logFlashcardUpdate($userId, $flashcard);
    }

    /**
     * Log a flashcard deletion event
     *
     * @throws JsonException
     */
    public function logFlashcardDeletion(int $userId, Flashcard $flashcard): Log
    {
        return $this->logRepository->logFlashcardDeletion($userId, $flashcard);
    }

    /**
     * Log flashcard list view.
     */
    public function logFlashcardList(int $userId): Log
    {
        return $this->logRepository->logFlashcardList($userId);
    }

    /**
     * Log flashcard restoration.
     */
    public function logFlashcardRestoration(int $userId, Flashcard $flashcard): Log
    {
        return $this->logRepository->logFlashcardRestoration($userId, $flashcard);
    }

    /**
     * Log a flashcard practice event
     *
     * @throws JsonException
     */
    public function logFlashcardPractice(int $userId, Flashcard $flashcard, bool $isCorrect): Log
    {
        return $this->logRepository->logFlashcardPractice($userId, $flashcard, $isCorrect);
    }

    /**
     * Log a study session start event
     *
     * @throws JsonException
     */
    public function logStudySessionStart(int $userId, StudySession $studySession): Log
    {
        return $this->logRepository->logStudySessionStart($userId, $studySession);
    }

    /**
     * Log a study session end event
     *
     * @throws JsonException
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log
    {
        return $this->logRepository->logStudySessionEnd($userId, $studySession);
    }

    /**
     * Log statistics view.
     */
    public function logStatisticsView(int $userId): Log
    {
        return $this->logRepository->logStatisticsView($userId);
    }

    /**
     * Log a practice reset event
     *
     * @throws JsonException
     */
    public function logPracticeReset(int $userId): Log
    {
        return $this->logRepository->logPracticeReset($userId);
    }

    /**
     * Log user exit.
     */
    public function logUserExit(int $userId): Log
    {
        return $this->logRepository->logUserExit($userId);
    }

    /**
     * Log restoration of all flashcards.
     */
    public function logAllFlashcardsRestore(int $userId): Log
    {
        return $this->logRepository->logAllFlashcardsRestore($userId);
    }

    /**
     * Log permanent deletion of all flashcards.
     */
    public function logAllFlashcardsPermanentDelete(int $userId): Log
    {
        return $this->logRepository->logAllFlashcardsPermanentDelete($userId);
    }

    /**
     * Log import of flashcards from file.
     */
    public function logFlashcardImport(int $userId, int $importCount): Log
    {
        return $this->logRepository->logFlashcardImport($userId, $importCount);
    }

    /**
     * Log permanent deletion of a single flashcard.
     */
    public function logFlashcardForceDelete(int $userId, int $flashcardId, string $flashcardQuestion): Log
    {
        return $this->logRepository->logFlashcardForceDelete($userId, $flashcardId, $flashcardQuestion);
    }
}
