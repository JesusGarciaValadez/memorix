<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use Modules\Flashcard\app\Models\Statistic;

interface StatisticRepositoryInterface
{
    /**
     * Get statistics for a user.
     */
    public function getForUser(int $userId): ?Statistic;

    /**
     * Get statistics for a user.
     */
    public function getStatisticsForUser(int $userId): ?Statistic;

    /**
     * Create statistics for a user.
     */
    public function createForUser(int $userId): Statistic;

    /**
     * Increment flashcards created count.
     */
    public function incrementFlashcardsCreated(int $userId): bool;

    /**
     * Increment flashcards deleted count.
     */
    public function incrementFlashcardsDeleted(int $userId): bool;

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
    public function resetPracticeStats(int $userId): bool;

    /**
     * Reset practice statistics (alias for resetPracticeStats?).
     */
    public function resetPracticeStatistics(int $userId): bool;

    /**
     * Increment total flashcards count.
     */
    public function incrementTotalFlashcards(int $userId, int $count = 1): bool;

    /**
     * Decrement total flashcards count.
     */
    public function decrementTotalFlashcards(int $userId, int $count = 1): bool;

    /**
     * Get average study session duration.
     */
    public function getAverageStudySessionDuration(int $userId): float;

    /**
     * Get total study time.
     */
    public function getTotalStudyTime(int $userId): float;

    /**
     * Find statistics by user ID.
     */
    public function findByUserId(int $userId): ?Statistic;

    /**
     * Create statistics record.
     *
     * @param  array<string, mixed>  $data  // Add type hint for data if needed
     */
    public function create(array $data): Statistic; // Assuming create takes an array

    /**
     * Get practice success rate (correct / total answers).
     */
    public function getPracticeSuccessRate(int $userId): float;

    /**
     * Add minutes to the total study time for a user.
     */
    public function addStudyTime(int $userId, int $minutes): bool;
}
