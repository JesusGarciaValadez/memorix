<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
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
        private FlashcardRepositoryInterface $flashcardRepository,
        private StudySessionRepositoryInterface $studySessionRepository,
        private StatisticService $statisticService,
        private StudySessionService $studySessionService,
    ) {}

    public function execute(): void
    {
        clear();
        // Get all flashcards for the current user
        $userId = $this->command->user->id;
        $flashcards = $this->flashcardRepository->getAllForUser($userId, 100)->items();

        if (empty($flashcards)) {
            ConsoleRenderer::info('You have no flashcards to practice. Create some first!');

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
                ConsoleRenderer::success('Congratulations! You have correctly answered all flashcards.');
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
                ConsoleRenderer::error('Invalid selection. Please try again.');

                continue;
            }

            // Show the question
            ConsoleRenderer::info("Question: {$selectedFlashcard->question}");

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
                ConsoleRenderer::success('Correct answer!');
            } else {
                ConsoleRenderer::error("Incorrect. The correct answer was: {$selectedFlashcard->answer}");
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
        // Calculate completion percentage
        $totalCards = count($flashcards);
        $completedCards = count(array_filter($practiceResults, fn ($result) => $result['is_correct']));

        // Display title and completion statistics
        $this->command->line('Flashcard Practice Progress');
        $this->command->info("Completion: {$completedCards}/{$totalCards} cards");

        // Prepare table headers and rows
        $headers = ['Question', 'Status'];
        $rows = [];

        foreach ($flashcards as $flashcard) {
            $status = 'Not answered';
            if (isset($practiceResults[$flashcard->id])) {
                $status = $practiceResults[$flashcard->id]['is_correct'] ? 'Correct' : 'Incorrect';
            }

            $rows[] = [
                $flashcard->question,
                $status,
            ];
        }

        // Display the table
        $this->command->table($headers, $rows);
    }

    /**
     * Get the practice results for the flashcards.
     */
    private function getPracticeResults(array $flashcards): array
    {
        $results = [];

        foreach ($flashcards as $flashcard) {
            $practiceResults = $flashcard->practiceResults()
                ->orderBy('created_at', 'desc')
                ->first();

            if ($practiceResults) {
                $results[$flashcard->id] = [
                    'is_correct' => (bool) $practiceResults->is_correct,
                    'status' => $practiceResults->is_correct ? 'Correct' : 'Incorrect',
                ];
            }
        }

        return $results;
    }
}
