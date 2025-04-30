<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use App\Models\User;
use InvalidArgumentException;
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

        /* @var StudySession $session */
        foreach ($completedSessions as $session) {
            /* @var CarbonInterface $startedAt */
            $startedAt = $session->started_at;
            /* @var CarbonInterface $endedAt */
            $endedAt = $session->ended_at;
            $totalMinutes += $startedAt?->diffInMinutes($endedAt);
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

        /* @var StudySession $session */
        foreach ($completedSessions as $session) {
            /* @var CarbonInterface $startedAt */
            $startedAt = $session->started_at;
            /* @var CarbonInterface $endedAt */
            $endedAt = $session->ended_at;
            $totalMinutes += $startedAt?->diffInMinutes($endedAt);
        }

        return (float) $totalMinutes;
    }

    public function findByUserId(int $userId): ?Statistic
    {
        return Statistic::getForUser($userId);
    }

    public function create(array $data): Statistic
    {
        // Ensure user_id is present
        if (! isset($data['user_id'])) {
            throw new InvalidArgumentException('User ID is required to create statistics.');
        }

        // @phpstan-ignore-next-line cast.int
        $userId = (int) $data['user_id'];

        return Statistic::createForUser($userId);
    }

    public function incrementTotalFlashcards(int $userId, int $count = 1): bool
    {
        $statistic = Statistic::getForUser($userId);
        if (! $statistic instanceof Statistic) {
            return false;
        }

        return (bool) $statistic->increment('total_flashcards');
    }

    public function decrementTotalFlashcards(int $userId, int $count = 1): bool
    {
        $statistic = Statistic::getForUser($userId);

        // Separate checks for clarity and type safety
        if (! $statistic instanceof Statistic) {
            return false;
        }
        if ($statistic->total_flashcards <= 0) {
            return false;
        }

        return (bool) $statistic->decrement('total_flashcards');
    }

    public function getPracticeSuccessRate(int $userId): float
    {
        $statistic = Statistic::getForUser($userId);
        if (! $statistic instanceof Statistic) {
            return 0.0;
        }

        $total = $statistic->total_correct_answers + $statistic->total_incorrect_answers;

        return $total > 0 ? round(($statistic->total_correct_answers / $total) * 100, 2) : 0.0;
    }

    /**
     * Add minutes to the total study time for a user.
     */
    public function addStudyTime(int $userId, int $minutes): bool
    {
        $statistic = $this->getOrCreateForUser($userId);

        // Ensure minutes is non-negative
        if ($minutes < 0) {
            return false;
        }

        // Use increment method for atomic update
        return (bool) $statistic->increment('study_time', $minutes);
    }

    public function getCorrectAnswersCount(int $userId): int
    {
        $statistic = Statistic::getForUser($userId);

        return $statistic instanceof Statistic ? $statistic->total_correct_answers : 0;
    }

    public function getIncorrectAnswersCount(int $userId): int
    {
        $statistic = Statistic::getForUser($userId);

        return $statistic instanceof Statistic ? $statistic->total_incorrect_answers : 0;
    }

    /**
     * @param  array<string, int>  $data
     */
    public function updateStatistics(int $userId, array $data): bool
    {
        $statistic = Statistic::getForUser($userId);
        if (! $statistic instanceof Statistic) {
            return false;
        }

        return $statistic->update($data);
    }

    public function resetPracticeStatistics(int $userId): bool
    {
        return Statistic::resetPracticeStats($userId);
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
