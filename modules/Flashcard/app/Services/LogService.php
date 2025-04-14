<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
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
     */
    public function createLog(int $userId, string $action, string $description, array $details = []): Log
    {
        return $this->logRepository->createLog($userId, $action, $description, $details);
    }

    /**
     * Log a flashcard creation event
     */
    public function logFlashcardCreation(int $userId, Flashcard $flashcard): Log
    {
        return $this->createLog(
            $userId,
            'flashcard_created',
            'Created a new flashcard',
            ['flashcard_id' => $flashcard->id]
        );
    }

    /**
     * Log a flashcard update event
     */
    public function logFlashcardUpdate(int $userId, Flashcard $flashcard): Log
    {
        return $this->createLog(
            $userId,
            'flashcard_updated',
            'Updated a flashcard',
            ['flashcard_id' => $flashcard->id]
        );
    }

    /**
     * Log a flashcard deletion event
     */
    public function logFlashcardDeletion(int $userId, Flashcard $flashcard): Log
    {
        return $this->createLog(
            $userId,
            'flashcard_deleted',
            'Deleted a flashcard',
            ['flashcard_id' => $flashcard->id]
        );
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
     */
    public function logFlashcardPractice(int $userId, Flashcard $flashcard, bool $isCorrect): Log
    {
        return $this->createLog(
            $userId,
            'flashcard_practiced',
            'Practiced a flashcard',
            [
                'flashcard_id' => $flashcard->id,
                'result' => $isCorrect ? 'correct' : 'incorrect',
            ]
        );
    }

    /**
     * Log a study session start event
     */
    public function logStudySessionStart(int $userId, StudySession $studySession): Log
    {
        return $this->createLog(
            $userId,
            'study_session_started',
            'Started a study session',
            ['session_id' => $studySession->id]
        );
    }

    /**
     * Log a study session end event
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log
    {
        return $this->createLog(
            $userId,
            'study_session_ended',
            'Ended a study session',
            [
                'session_id' => $studySession->id,
                'duration' => $studySession->duration,
            ]
        );
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
     */
    public function logPracticeReset(int $userId): Log
    {
        return $this->createLog(
            $userId,
            'practice_reset',
            'Reset practice progress'
        );
    }

    /**
     * Log an exit from the interactive command
     */
    public function logExit(User $user): Log
    {
        return $this->createLog(
            $user->id,
            'command_exit',
            'Exited interactive flashcard command'
        );
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
     * Log flashcard force delete
     */
    public function logFlashcardForceDelete(int $userId, int $flashcardId, string $flashcardQuestion): Log
    {
        return $this->logRepository->logFlashcardForceDelete($userId, $flashcardId, $flashcardQuestion);
    }

    /**
     * Log import of flashcards from file.
     */
    public function logFlashcardImport(int $userId, int $importCount): Log
    {
        return $this->logRepository->logFlashcardImport($userId, $importCount);
    }
}
