<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;

final class PracticeResultRepository implements PracticeResultRepositoryInterface
{
    /**
     * Create a new practice result.
     */
    public function create(int $userId, int $flashcardId, int $studySessionId, bool $isCorrect): bool
    {
        return DB::table('practice_results')->insert([
            'user_id' => $userId,
            'flashcard_id' => $flashcardId,
            'study_session_id' => $studySessionId,
            'is_correct' => $isCorrect,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get practice results for a user.
     *
     * @return Collection<int, PracticeResult>
     */
    public function getForUser(int $userId): Collection
    {
        return PracticeResult::where('user_id', $userId)->get();
    }

    /**
     * Get practice results for a flashcard.
     *
     * @return Collection<int, PracticeResult>
     */
    public function getForFlashcard(int $flashcardId): Collection
    {
        return PracticeResult::where('flashcard_id', $flashcardId)->get();
    }

    /**
     * Get practice results for a study session.
     *
     * @return Collection<int, PracticeResult>
     */
    public function getForStudySession(int $studySessionId): Collection
    {
        return PracticeResult::where('study_session_id', $studySessionId)->get();
    }

    /**
     * Delete all practice results for a user.
     */
    public function deleteForUser(int $userId): bool
    {
        return DB::table('practice_results')
            ->where('user_id', $userId)
            ->delete() >= 0;
    }

    /**
     * Check if a flashcard has been practiced in the last N days.
     */
    public function hasBeenPracticedRecently(int $flashcardId, int $days = 7): bool
    {
        return PracticeResult::where('flashcard_id', $flashcardId)
            ->where('created_at', '>', now()->subDays($days))
            ->exists();
    }

    /**
     * Get recently incorrect flashcards for a user.
     *
     * @return array<int, array{id: int, question: string, answer: string}>
     */
    public function getRecentlyIncorrectFlashcards(int $userId, int $days = 7, int $limit = 10): array
    {
        $incorrectFlashcards = DB::table('flashcards')
            ->join('practice_results', 'flashcards.id', '=', 'practice_results.flashcard_id')
            ->where('flashcards.user_id', $userId)
            ->where('practice_results.is_correct', false)
            ->where('practice_results.created_at', '>', now()->subDays($days))
            ->select('flashcards.*')
            ->orderBy('practice_results.created_at', 'desc')
            ->limit($limit)
            ->get();

        return $incorrectFlashcards->map(fn ($flashcard): array => [
            'id' => $flashcard->id,
            'question' => $flashcard->question,
            'answer' => $flashcard->answer,
        ])->toArray();
    }
}
