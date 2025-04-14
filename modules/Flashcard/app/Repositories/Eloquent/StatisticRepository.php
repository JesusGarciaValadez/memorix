<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;

final class StatisticRepository implements StatisticRepositoryInterface
{
    /**
     * Get statistics for a user.
     */
    public function getForUser(int $userId): ?Statistic
    {
        return Statistic::where('user_id', $userId)->first();
    }

    /**
     * Get statistics for a user.
     */
    public function getStatisticsForUser(int $userId): ?Statistic
    {
        return $this->getForUser($userId);
    }

    /**
     * Create statistics for a user.
     */
    public function createForUser(int $userId): Statistic
    {
        return Statistic::create([
            'user_id' => $userId,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    /**
     * Increment flashcards created count.
     */
    public function incrementFlashcardsCreated(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        return (bool) $statistic->increment('total_flashcards');
    }

    /**
     * Increment flashcards deleted count.
     */
    public function incrementFlashcardsDeleted(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        // We don't decrement total_flashcards since the count is still important for stats
        return $statistic->update([
            'updated_at' => now(),
        ]);
    }

    /**
     * Increment study sessions count.
     */
    public function incrementStudySessions(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        return (bool) $statistic->increment('total_study_sessions');
    }

    /**
     * Increment correct answers count.
     */
    public function incrementCorrectAnswers(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        return (bool) $statistic->increment('total_correct_answers');
    }

    /**
     * Increment incorrect answers count.
     */
    public function incrementIncorrectAnswers(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        return (bool) $statistic->increment('total_incorrect_answers');
    }

    /**
     * Reset practice statistics.
     */
    public function resetPracticeStats(int $userId): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        return $statistic->update([
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    /**
     * Get average study session duration.
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
            // Use started_at->diffInMinutes($ended_at) to get positive minutes
            $totalMinutes += $session->started_at->diffInMinutes($session->ended_at);
        }

        return round($totalMinutes / count($completedSessions), 2);
    }

    /**
     * Get total study time.
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
            // Use started_at->diffInMinutes($ended_at) to get positive minutes
            $totalMinutes += $session->started_at->diffInMinutes($session->ended_at);
        }

        return (float) $totalMinutes;
    }

    /**
     * Get or create statistics for a user.
     */
    private function getOrCreateForUser(int $userId): Statistic
    {
        $statistic = $this->getForUser($userId);

        if (! $statistic instanceof Statistic) {
            return $this->createForUser($userId);
        }

        return $statistic;
    }
}
