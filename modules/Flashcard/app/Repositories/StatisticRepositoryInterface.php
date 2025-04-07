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
     * Get average study session duration.
     */
    public function getAverageStudySessionDuration(int $userId): float;

    /**
     * Get total study time.
     */
    public function getTotalStudyTime(int $userId): float;
}
