<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StudySessionService;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;

final readonly class PracticeFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private StudySessionRepositoryInterface $studySessionRepository,
        private StatisticService $statisticService,
        private StudySessionService $studySessionService,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        clear();
        // Get all flashcards for the current user
        $userId = $this->command->user->id;
        $flashcards = Flashcard::getAllForUser($userId, 100)->items();

        if (empty($flashcards)) {
            $this->renderer->info('You have no flashcards to practice. Create some first!');

            return;
        }

        // Start a new study session using the service
        $studySession = $this->studySessionService->startSession($userId);
        $practiceResults = $this->getPracticeResults($flashcards);
        $shouldContinue = true;

        while ($shouldContinue) {
            // Display current progress
            $this->showProgress($flashcards, $practiceResults);

            // Get flashcards that are not correctly answered
            $availableFlashcards = array_filter($flashcards, static function ($flashcard) use ($practiceResults) {
                return ! isset($practiceResults[$flashcard->id]) || ! $practiceResults[$flashcard->id]['is_correct'];
            });

            if (empty($availableFlashcards)) {
                $this->renderer->success('Congratulations! You have correctly answered all flashcards.');
                break;
            }

            // Let user select a flashcard to practice
            $flashcardOptions = [];
            foreach ($availableFlashcards as $flashcard) {
                $status = ! isset($practiceResults[$flashcard->id])
                    ? 'Not answered'
                    : ($practiceResults[$flashcard->id]['is_correct'] ? 'Correct' : 'Incorrect');

                $flashcardOptions[$flashcard->id] = "Question: {$flashcard->question} (Status: {$status})";
            }

            $flashcardOptions['exit'] = 'Return to main menu';

            $selectedOption = $this->command->choice(
                'Select a flashcard to practice:',
                $flashcardOptions
            );

            if ($selectedOption === 'exit') {
                $shouldContinue = false;

                continue;
            }

            // Find the selected flashcard
            $selectedFlashcard = collect($flashcards)->firstWhere('id', $selectedOption);

            if (! $selectedFlashcard) {
                $this->renderer->error('Invalid selection. Please try again.');

                continue;
            }

            // Show the question
            $this->renderer->info("Question: {$selectedFlashcard->question}");

            // Get user's answer
            $answer = $this->command->ask('Your answer:');

            // Check if the answer is correct (case-insensitive)
            $isCorrect = mb_strtolower(mb_trim($answer)) === mb_strtolower(mb_trim($selectedFlashcard->answer));

            // Update practice results
            $practiceResults[$selectedFlashcard->id] = [
                'is_correct' => $isCorrect,
                'status' => $isCorrect ? 'Correct' : 'Incorrect',
            ];

            // Record the practice result using the service
            $this->studySessionService->recordPracticeResult($userId, $selectedFlashcard->id, $isCorrect);

            // Show result message
            if ($isCorrect) {
                $this->renderer->success('Correct answer!');
            } else {
                $this->renderer->error("Incorrect. The correct answer was: {$selectedFlashcard->answer}");
            }
        }

        // End the study session using the service
        $this->studySessionService->endSession($userId, $studySession->id);
    }

    /**
     * Show the current progress of flashcard practice.
     */
    private function showProgress(array $flashcards, array $practiceResults): void
    {
        $rows = [];
        $totalFlashcards = count($flashcards);
        $correctAnswers = 0;
        $incorrectAnswers = 0;

        foreach ($flashcards as $flashcard) {
            $status = 'Not answered';
            if (isset($practiceResults[$flashcard->id])) {
                if ($practiceResults[$flashcard->id]['is_correct']) {
                    $status = 'Correct';
                    $correctAnswers++;
                } else {
                    $status = 'Incorrect';
                    $incorrectAnswers++;
                }
            }

            $rows[] = [
                'Question' => $flashcard->question,
                'Status' => $status,
            ];
        }

        // Calculate completion percentage
        $completionPercentage = ($correctAnswers / $totalFlashcards) * 100;

        // Display progress table
        table(
            headers: ['Question', 'Status'],
            rows: $rows
        );

        // Display summary
        $this->renderer->info(sprintf(
            "\nProgress: %.1f%% complete (%d/%d flashcards answered correctly)",
            $completionPercentage,
            $correctAnswers,
            $totalFlashcards
        ));
    }

    /**
     * Get practice results for all flashcards.
     *
     * @param  array<Flashcard>  $flashcards
     * @return array<int, array{is_correct: bool, status: string}>
     */
    private function getPracticeResults(array $flashcards): array
    {
        $results = [];
        foreach ($flashcards as $flashcard) {
            $practiceResult = $this->studySessionRepository->getLatestResultForFlashcard($flashcard->id);
            if ($practiceResult) {
                $results[$flashcard->id] = [
                    'is_correct' => $practiceResult->is_correct,
                    'status' => $practiceResult->is_correct ? 'Correct' : 'Incorrect',
                ];
            }
        }

        return $results;
    }
}
