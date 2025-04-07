<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Services\StatisticService;

use function Laravel\Prompts\table;

final readonly class StatisticsFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private ?StatisticService $statisticService = null,
    ) {
        $this->statisticService ??= app(StatisticService::class);
    }

    public function execute(): void
    {
        $this->command->info('Showing statistics...');

        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->user;
        }

        if (! $user) {
            ConsoleRenderer::error('You must be logged in to view statistics.');

            return;
        }

        // Get statistics for the current user
        $stats = $this->statisticService->getStatisticsForUser($user->id);
        $successRate = $this->statisticService->getPracticeSuccessRate($user->id);
        $avgSessionDuration = $this->statisticService->getAverageStudySessionDuration($user->id);
        $totalStudyTime = $this->statisticService->getTotalStudyTime($user->id);

        // Count total flashcards
        $totalFlashcards = $stats['flashcards_created'];

        // Calculate completion percentage
        $answeredFlashcards = $stats['correct_answers'] + $stats['incorrect_answers'];
        $completionPercentage = $totalFlashcards > 0
            ? round(($answeredFlashcards / $totalFlashcards) * 100, 2)
            : 0;

        // Display the statistics using a table
        table(
            headers: ['Statistic', 'Value'],
            rows: [
                ['Total Flashcards', $totalFlashcards],
                ['Study Sessions', $stats['study_sessions']],
                ['Correct Answers', $stats['correct_answers']],
                ['Incorrect Answers', $stats['incorrect_answers']],
                ['Success Rate', $successRate.'%'],
                ['Completion', $completionPercentage.'%'],
                ['Avg. Session Duration', $avgSessionDuration.' minutes'],
                ['Total Study Time', $totalStudyTime.' minutes'],
            ]
        );

        // Display some useful insights based on the statistics
        if ($totalFlashcards === 0) {
            ConsoleRenderer::warning('You have not created any flashcards yet.');
        } elseif ($answeredFlashcards === 0) {
            ConsoleRenderer::warning('You have not practiced any flashcards yet.');
        } elseif ($successRate < 50) {
            ConsoleRenderer::warning('Your success rate is below 50%. Keep practicing to improve!');
        } elseif ($successRate >= 80) {
            ConsoleRenderer::success('Great job! Your success rate is '.$successRate.'%.');
        }
    }
}
