<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

final class StudySessionRepository implements StudySessionRepositoryInterface
{
    /**
     * Start a new study session.
     */
    public function startSession(int $userId): StudySession
    {
        return StudySession::create([
            'user_id' => $userId,
            'started_at' => now(),
            'ended_at' => null,
        ]);
    }

    /**
     * End a study session.
     */
    public function endSession(StudySession $studySession): bool
    {
        return $studySession->update([
            'ended_at' => now(),
        ]);
    }

    /**
     * Find a study session for a user.
     */
    public function findForUser(int $sessionId, int $userId): ?StudySession
    {
        return StudySession::where('id', $sessionId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get active session for a user.
     */
    public function getActiveSessionForUser(int $userId): ?StudySession
    {
        return StudySession::where('user_id', $userId)
            ->whereNull('ended_at')
            ->orderBy('started_at', 'desc')
            ->first();
    }

    /**
     * Get flashcards for practice.
     */
    public function getFlashcardsForPractice(int $userId): array
    {
        // First get recently incorrect answers
        $incorrectFlashcards = DB::table('flashcards')
            ->join('practice_results', 'flashcards.id', '=', 'practice_results.flashcard_id')
            ->where('flashcards.user_id', $userId)
            ->where('practice_results.is_correct', false)
            ->where('practice_results.created_at', '>', now()->subDays(7))
            ->select('flashcards.*')
            ->orderBy('practice_results.created_at', 'desc')
            ->limit(10)
            ->get();

        // Then get cards that haven't been practiced recently
        $unpracticedFlashcards = Flashcard::where('user_id', $userId)
            ->whereNotIn('id', $incorrectFlashcards->pluck('id'))
            ->whereDoesntHave('practiceResults', function ($query) {
                $query->where('created_at', '>', now()->subDays(7));
            })
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Combine both sets
        $flashcards = $incorrectFlashcards->merge($unpracticedFlashcards);

        if ($flashcards->isEmpty()) {
            // If no specific cards to practice, get 10 random cards
            $flashcards = Flashcard::where('user_id', $userId)
                ->inRandomOrder()
                ->limit(10)
                ->get();
        }

        return $flashcards->map(function ($flashcard) {
            return [
                'id' => $flashcard->id,
                'question' => $flashcard->question,
                'answer' => $flashcard->answer,
            ];
        })->toArray();
    }

    /**
     * Record practice result.
     */
    public function recordPracticeResult(int $userId, int $flashcardId, bool $isCorrect): bool
    {
        $session = $this->getActiveSessionForUser($userId);

        if (! $session) {
            $session = $this->startSession($userId);
        }

        return DB::table('practice_results')->insert([
            'user_id' => $userId,
            'flashcard_id' => $flashcardId,
            'study_session_id' => $session->id,
            'is_correct' => $isCorrect,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reset practice progress.
     */
    public function resetPracticeProgress(int $userId): bool
    {
        // End active session if exists
        $activeSession = $this->getActiveSessionForUser($userId);
        if ($activeSession) {
            $this->endSession($activeSession);
        }

        // Delete practice results
        return DB::table('practice_results')
            ->where('user_id', $userId)
            ->delete() >= 0;
    }
}
