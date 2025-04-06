<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

final class StudySessionRepository implements StudySessionRepositoryInterface
{
    public function __construct(
        private readonly PracticeResultRepositoryInterface $practiceResultRepository
    ) {}

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
        $incorrectFlashcards = $this->practiceResultRepository->getRecentlyIncorrectFlashcards($userId);

        $incorrectIds = array_column($incorrectFlashcards, 'id');

        // Then get cards that haven't been practiced recently
        $unpracticedFlashcards = Flashcard::where('user_id', $userId)
            ->whereNotIn('id', $incorrectIds)
            ->whereNot(function ($query) {
                $query->whereHas('practiceResults', function ($query) {
                    $query->where('created_at', '>', now()->subDays(7));
                });
            })
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($flashcard) {
                return [
                    'id' => $flashcard->id,
                    'question' => $flashcard->question,
                    'answer' => $flashcard->answer,
                ];
            })
            ->toArray();

        // Combine both sets
        $flashcards = array_merge($incorrectFlashcards, $unpracticedFlashcards);

        if (empty($flashcards)) {
            // If no specific cards to practice, get 10 random cards
            $flashcards = Flashcard::where('user_id', $userId)
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->map(function ($flashcard) {
                    return [
                        'id' => $flashcard->id,
                        'question' => $flashcard->question,
                        'answer' => $flashcard->answer,
                    ];
                })
                ->toArray();
        }

        return $flashcards;
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

        return $this->practiceResultRepository->create(
            $userId,
            $flashcardId,
            $session->id,
            $isCorrect
        );
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
        return $this->practiceResultRepository->deleteForUser($userId);
    }
}
