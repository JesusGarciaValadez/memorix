<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use App\Models\User;
use JsonException;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final class LogRepository implements LogRepositoryInterface
{
    /**
     * Get logs for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return Log::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (Log $log) => [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'action' => $log->action,
                'level' => $log->level,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'details' => $log->details,
            ])
            ->all();
    }

    /**
     * Log user login.
     */
    public function logUserLogin(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::logUserLogin($user);
    }

    /**
     * Log flashcard creation.
     *
     * @throws JsonException
     */
    public function logFlashcardCreation(int $userId, Flashcard $flashcard): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'created_flashcard',
            Log::LEVEL_INFO,
            "Created flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
        );
    }

    /**
     * Log flashcard update.
     */
    public function logFlashcardUpdate(int $userId, Flashcard $flashcard): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'updated_flashcard',
            Log::LEVEL_INFO,
            "Updated flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
        );
    }

    /**
     * Log flashcard deletion.
     */
    public function logFlashcardDeletion(int $userId, Flashcard $flashcard): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'deleted_flashcard',
            Log::LEVEL_WARNING,
            "Deleted flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
        );
    }

    /**
     * Log flashcard list view.
     */
    public function logFlashcardList(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'viewed_flashcard_list',
            Log::LEVEL_DEBUG,
            'User viewed flashcard list'
        );
    }

    /**
     * Log flashcard restoration.
     */
    public function logFlashcardRestoration(int $userId, Flashcard $flashcard): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'restored_flashcard',
            Log::LEVEL_INFO,
            "Restored flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
        );
    }

    /**
     * Log flashcard practice.
     */
    public function logFlashcardPractice(int $userId, Flashcard $flashcard, bool $isCorrect): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            $isCorrect ? 'flashcard_answered_correctly' : 'flashcard_answered_incorrectly',
            $isCorrect ? Log::LEVEL_INFO : Log::LEVEL_WARNING,
            "Answered flashcard ID: {$flashcard->id}, Result: ".($isCorrect ? 'Correct' : 'Incorrect')
        );
    }

    /**
     * Log study session start.
     */
    public function logStudySessionStart(int $userId, StudySession $studySession): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'started_study_session',
            Log::LEVEL_INFO,
            "Started study session ID: {$studySession->id}"
        );
    }

    /**
     * Log study session end.
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'ended_study_session',
            Log::LEVEL_INFO,
            "Ended study session ID: {$studySession->id}"
        );
    }

    /**
     * Log statistics view.
     */
    public function logStatisticsView(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::logStatisticsView($user);
    }

    /**
     * Log practice reset.
     */
    public function logPracticeReset(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'practice_reset',
            Log::LEVEL_WARNING,
            null
        );
    }

    /**
     * Log user exit.
     */
    public function logUserExit(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::logUserExit($user);
    }

    /**
     * Log restoration of all flashcards.
     */
    public function logAllFlashcardsRestore(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'restored_all_flashcards',
            Log::LEVEL_INFO,
            'Restored all deleted flashcards'
        );
    }

    /**
     * Log permanent deletion of all flashcards.
     */
    public function logAllFlashcardsPermanentDelete(int $userId): Log
    {
        $user = User::findOrFail($userId);

        return Log::createEntry(
            $user,
            'permanently_deleted_all_flashcards',
            Log::LEVEL_WARNING,
            'Permanently deleted all flashcards'
        );
    }
}
