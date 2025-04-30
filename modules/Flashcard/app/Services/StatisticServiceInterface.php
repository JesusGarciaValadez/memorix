<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;

interface StatisticServiceInterface
{
    /**
     * Get statistics for a user.
     */
    public function getByUserId(int $userId): ?Statistic;

    /**
     * Get statistics for a user.
     *
     * @return array<string, int>
     */
    public function getStatisticsForUser(int $userId): array;

    /**
     * Get practice success rate for a user.
     */
    public function getPracticeSuccessRate(int $userId): float;

    /**
     * Create statistics for a user.
     */
    public function createStatistic(int $userId): Statistic;

    /**
     * Increment total flashcards count.
     */
    public function incrementTotalFlashcards(int $userId): bool;

    /**
     * Decrement total flashcards count.
     */
    public function decrementTotalFlashcards(int $userId): bool;

    /**
     * Increment study sessions count.
     */
    public function incrementStudySessions(int $userId): bool;

    /**
     * Increment correct answers count.
     */
    public function incrementCorrectAnswers(int $userId): bool;

    /**
     * Increment incorrect answers count.
     */
    public function incrementIncorrectAnswers(int $userId): bool;

    /**
     * Reset practice statistics.
     */
    public function resetPracticeStatistics(int $userId): bool;

    /**
     * Update statistics for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateStatistics(int $userId, array $data): bool;

    /**
     * Add study time to user statistics.
     */
    public function addStudyTime(User $user, int $minutes): bool;

    /**
     * Get average study session duration.
     */
    public function getAverageStudySessionDuration(int $userId): float;

    /**
     * Get total study time.
     */
    public function getTotalStudyTime(int $userId): float;
}
