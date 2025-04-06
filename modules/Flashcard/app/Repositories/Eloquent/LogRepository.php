<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use JsonException;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final class LogRepository implements LogRepositoryInterface
{
    /**
     * Get logs for a user.
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return Log::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Log flashcard creation.
     *
     * @throws JsonException
     */
    public function logFlashcardCreation(int $userId, Flashcard $flashcard): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'flashcard_created',
            'details' => json_encode([
                'flashcard_id' => $flashcard->id,
                'question' => $flashcard->question,
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    /**
     * Log flashcard update.
     */
    public function logFlashcardUpdate(int $userId, Flashcard $flashcard): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'flashcard_updated',
            'details' => json_encode([
                'flashcard_id' => $flashcard->id,
                'question' => $flashcard->question,
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    /**
     * Log flashcard deletion.
     */
    public function logFlashcardDeletion(int $userId, Flashcard $flashcard): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'flashcard_deleted',
            'details' => json_encode([
                'flashcard_id' => $flashcard->id,
                'question' => $flashcard->question,
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    /**
     * Log flashcard restoration.
     */
    public function logFlashcardRestoration(int $userId, Flashcard $flashcard): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'flashcard_restored',
            'details' => json_encode([
                'flashcard_id' => $flashcard->id,
                'question' => $flashcard->question,
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Log flashcard practice.
     */
    public function logFlashcardPractice(int $userId, Flashcard $flashcard, bool $isCorrect): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => $isCorrect ? 'flashcard_answered_correctly' : 'flashcard_answered_incorrectly',
            'details' => json_encode([
                'flashcard_id' => $flashcard->id,
                'question' => $flashcard->question,
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Log study session start.
     */
    public function logStudySessionStart(int $userId, StudySession $studySession): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'study_session_started',
            'details' => json_encode([
                'study_session_id' => $studySession->id,
                'started_at' => $studySession->started_at,
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Log study session end.
     */
    public function logStudySessionEnd(int $userId, StudySession $studySession): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'study_session_ended',
            'details' => json_encode([
                'study_session_id' => $studySession->id,
                'started_at' => $studySession->started_at,
                'ended_at' => $studySession->ended_at,
                'duration' => $studySession->ended_at->diffInMinutes($studySession->started_at),
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Log practice reset.
     */
    public function logPracticeReset(int $userId): Log
    {
        return Log::create([
            'user_id' => $userId,
            'action' => 'practice_reset',
            'details' => null,
            'created_at' => now(),
        ]);
    }
}
