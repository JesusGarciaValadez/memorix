<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

final readonly class PracticeFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private FlashcardRepositoryInterface $flashcardRepository,
        private StudySessionRepositoryInterface $studySessionRepository
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

            $selectedOption = select(
                label: 'Select a flashcard to practice:',
                options: $flashcardOptions,
                scroll: 10
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

            // Show question and ask for answer
            ConsoleRenderer::info("Question: {$selectedFlashcard->question}");
            $userAnswer = text(
                label: 'Your answer:',
                required: true
            );

            // Check if the answer is correct (case-insensitive comparison)
            $isCorrect = mb_strtolower(mb_trim($userAnswer)) === mb_strtolower(mb_trim($selectedFlashcard->answer));

            // Record the practice result
            $this->studySessionRepository->recordPracticeResult($userId, $selectedFlashcard->id, $isCorrect);

            // Update local practice results
            $practiceResults[$selectedFlashcard->id] = [
                'is_correct' => $isCorrect,
                'status' => $isCorrect ? 'Correct' : 'Incorrect',
            ];

            // Show feedback
            if ($isCorrect) {
                ConsoleRenderer::success('Correct!');
            } else {
                ConsoleRenderer::error("Incorrect. The correct answer is: {$selectedFlashcard->answer}");
            }
        }
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
