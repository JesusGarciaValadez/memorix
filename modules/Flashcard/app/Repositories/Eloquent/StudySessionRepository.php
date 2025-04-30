<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use stdClass;

final readonly class StudySessionRepository implements StudySessionRepositoryInterface
{
    public function __construct(
        private PracticeResultRepositoryInterface $practiceResultRepository
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
        $studySession->end();

        return true;
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
     * Fetch flashcards for a practice session, prioritizing those answered incorrectly.
     *
     * @return array<int, array{id: int, question: string, answer: string}>
     */
    public function getFlashcardsForPractice(int $userId, int $limit = 10): array
    {
        $results = Flashcard::query()
            ->select('flashcards.id', 'flashcards.question', 'flashcards.answer')
            ->leftJoin('practice_results', function ($join) use ($userId): void {
                $join->on('flashcards.id', '=', 'practice_results.flashcard_id')
                    ->where('practice_results.user_id', '=', $userId)
                    ->whereRaw('practice_results.id = (select max(id) from practice_results where flashcard_id = flashcards.id and user_id = ?)', [$userId]);
            })
            ->where('flashcards.user_id', $userId)
            ->orderByRaw('CASE WHEN practice_results.is_correct = 0 THEN 0 ELSE 1 END ASC')
            ->orderBy('practice_results.created_at', 'asc')
            ->orderBy('flashcards.created_at', 'asc')
            ->limit($limit)
            ->distinct('flashcards.id')
            ->get()
            ->all(); // Get results as an array

        $flashcards = [];
        foreach ($results as $result) {
            // Check if properties exist and add type hint
            if (isset($result->id, $result->question, $result->answer)) {
                /** @var object{id: int, question: string, answer: string} $result */
                $flashcards[] = [
                    'id' => $result->id,
                    'question' => $result->question,
                    'answer' => $result->answer,
                ];
            }
        }

        return $flashcards;
    }

    /**
     * Record practice result.
     */
    public function recordPracticeResult(int $userId, int $flashcardId, bool $isCorrect): bool
    {
        $session = $this->getActiveSessionForUser($userId);

        if (! $session instanceof StudySession) {
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
        if ($activeSession instanceof StudySession) {
            $this->endSession($activeSession);
        }

        // Delete practice results
        return $this->practiceResultRepository->deleteForUser($userId);
    }

    /**
     * Delete all study sessions for a user.
     */
    public function deleteAllForUser(int $userId): bool
    {
        return DB::table('study_sessions')
            ->where('user_id', $userId)
            ->delete() >= 0;
    }

    /**
     * Get the latest practice result for a flashcard.
     *
     * @return array{id: int, flashcard_id: int, study_session_id: int, is_correct: bool, created_at: string}|null
     */
    public function getLatestResultForFlashcard(int $flashcardId): ?array
    {
        /** @var stdClass|null $result */
        $result = DB::table('practice_results')
            ->where('flashcard_id', $flashcardId)
            ->orderBy('created_at', 'desc')
            ->select(['id', 'flashcard_id', 'study_session_id', 'is_correct', 'created_at'])
            ->first();

        if (! $result) {
            return null;
        }

        return [
            'id' => $result->id,
            'flashcard_id' => $result->flashcard_id,
            'study_session_id' => $result->study_session_id,
            'is_correct' => (bool) $result->is_correct,
            'created_at' => $result->created_at,
        ];
    }
}
