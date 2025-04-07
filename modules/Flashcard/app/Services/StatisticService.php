<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;

final readonly class StatisticService
{
    public function __construct(
        private StatisticRepositoryInterface $statisticRepository,
    ) {}

    /**
     * Get statistics for a user.
     */
    public function getStatisticsForUser(int $userId): array
    {
        $statistics = $this->statisticRepository->getForUser($userId);

        // If no statistics exist yet, create an empty statistics record
        if (! $statistics) {
            $statistics = $this->statisticRepository->createForUser($userId);
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
        $statistics = $this->statisticRepository->getForUser($userId);

        if (! $statistics) {
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
        return $this->statisticRepository->getAverageStudySessionDuration($userId);
    }

    /**
     * Get total study time for a user.
     */
    public function getTotalStudyTime(int $userId): float
    {
        return $this->statisticRepository->getTotalStudyTime($userId);
    }
}
