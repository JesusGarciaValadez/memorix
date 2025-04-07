<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;

final readonly class StatisticsFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private StatisticRepositoryInterface $statisticRepository,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->user;
        }

        if (! $user) {
            $this->renderer->error('You must be logged in to view statistics.');

            return;
        }

        $stats = $this->statisticRepository->getStatisticsForUser($user->id);

        if (! $stats) {
            $this->renderer->warning('No statistics available yet.');

            return;
        }

        $this->renderer->info('Your Flashcard Practice Statistics:');
        $this->renderer->info('--------------------------------');
        $this->renderer->info("Total Practice Sessions: {$stats->total_sessions}");
        $this->renderer->info("Total Questions Attempted: {$stats->total_questions_attempted}");
        $this->renderer->info("Correct Answers: {$stats->correct_answers}");
        $this->renderer->info("Incorrect Answers: {$stats->incorrect_answers}");
        $accuracy = $stats->total_questions_attempted > 0
            ? round(($stats->correct_answers / $stats->total_questions_attempted) * 100, 2)
            : 0;
        $this->renderer->info("Accuracy Rate: {$accuracy}%");
        $this->renderer->info("Average Time Per Question: {$stats->average_time_per_question} seconds");
        $this->renderer->info("Fastest Response Time: {$stats->fastest_response_time} seconds");
        $this->renderer->info("Slowest Response Time: {$stats->slowest_response_time} seconds");
        $this->renderer->info('--------------------------------');
    }
}
