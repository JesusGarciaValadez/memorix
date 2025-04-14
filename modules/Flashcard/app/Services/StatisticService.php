<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;

final class StatisticService implements StatisticServiceInterface
{
    /**
     * Get statistics for a user.
     */
    public function getStatisticsForUser(int $userId): array
    {
        $statistics = Statistic::getForUser($userId);

        // If no statistics exist yet, create an empty statistics record
        if (! $statistics instanceof Statistic) {
            $statistics = Statistic::createForUser($userId);
        }

        return [
            'flashcards_created' => $statistics->total_flashcards,
            'flashcards_deleted' => 0, // Set default value since this attribute doesn't exist
            'study_sessions' => $statistics->total_study_sessions,
            'correct_answers' => $statistics->total_correct_answers,
            'incorrect_answers' => $statistics->total_incorrect_answers,
        ];
    }

    /**
     * Get practice success rate for a user.
     */
    public function getPracticeSuccessRate(int $userId): float
    {
        $statistics = $this->getByUserId($userId);

        if (! $statistics instanceof Statistic) {
            return 0.0;
        }

        $totalAnswers = $statistics->total_correct_answers + $statistics->total_incorrect_answers;

        if ($totalAnswers === 0) {
            return 0.0;
        }

        return round(($statistics->total_correct_answers / $totalAnswers) * 100, 2);
    }

    /**
     * Get average study session duration for a user.
     */
    public function getAverageStudySessionDuration(int $userId): float
    {
        $completedSessions = StudySession::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->get();

        if ($completedSessions->isEmpty()) {
            return 0.0;
        }

        $totalMinutes = 0;

        foreach ($completedSessions as $session) {
            $totalMinutes += $session->started_at->diffInMinutes($session->ended_at);
        }

        return round($totalMinutes / count($completedSessions), 2);
    }

    /**
     * Get total study time for a user.
     */
    public function getTotalStudyTime(int $userId): float
    {
        $completedSessions = StudySession::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->get();

        if ($completedSessions->isEmpty()) {
            return 0.0;
        }

        $totalMinutes = 0;

        foreach ($completedSessions as $session) {
            $totalMinutes += $session->started_at->diffInMinutes($session->ended_at);
        }

        return (float) $totalMinutes;
    }

    /**
     * Get statistic by user id
     */
    public function getByUserId(int $userId): ?Statistic
    {
        return Statistic::where('user_id', $userId)->first();
    }

    /**
     * Create a new statistic
     */
    public function createStatistic(int $userId): Statistic
    {
        return Statistic::create([
            'user_id' => $userId,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    public function incrementCorrectAnswers(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        return (bool) $statistic->increment('total_correct_answers');
    }

    public function incrementIncorrectAnswers(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        return (bool) $statistic->increment('total_incorrect_answers');
    }

    public function incrementStudySessions(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        return (bool) $statistic->increment('total_study_sessions');
    }

    public function incrementTotalFlashcards(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        return (bool) $statistic->increment('total_flashcards');
    }

    public function decrementTotalFlashcards(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        if ($statistic->total_flashcards > 0) {
            return (bool) $statistic->decrement('total_flashcards');
        }

        return true;
    }

    public function addStudyTime(User $user, int $minutes): bool
    {
        $this->getOrCreateStatistic($user->id);

        // Create a new study session record
        $studySession = new StudySession([
            'user_id' => $user->id,
            'started_at' => now()->subMinutes($minutes),
            'ended_at' => now(),
        ]);

        return $studySession->save();
    }

    /**
     * Reset practice statistics
     *
     * @return Statistic
     */
    public function resetPracticeStatistics(int $userId): bool
    {
        $statistic = $this->getOrCreateStatistic($userId);

        return $statistic->update([
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    /**
     * Update statistics for a user.
     */
    public function updateStatistics(int $userId, array $data): bool
    {
        $statistics = Statistic::getForUser($userId);

        if (! $statistics instanceof Statistic) {
            return false;
        }

        return $statistics->update([
            'total_correct_answers' => $data['correct_answers'] ?? $statistics->total_correct_answers,
            'total_incorrect_answers' => $data['incorrect_answers'] ?? $statistics->total_incorrect_answers,
        ]);
    }

    /**
     * Get or create statistics for a user.
     */
    private function getOrCreateStatistic(int $userId): Statistic
    {
        $statistic = $this->getByUserId($userId);

        if (! $statistic instanceof Statistic) {
            return $this->createStatistic($userId);
        }

        return $statistic;
    }
}
