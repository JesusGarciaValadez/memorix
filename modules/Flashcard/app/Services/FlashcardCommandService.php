<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

/**
 * A unified service class that consolidates the functionality previously spread
 * across multiple small Action classes. This reduces unnecessary abstraction
 * while maintaining separation of concerns between UI and business logic.
 */
final class FlashcardCommandService implements FlashcardCommandServiceInterface
{
    public function __construct(
        private readonly FlashcardService $flashcardService,
        private readonly StudySessionService $studySessionService,
        private readonly StatisticServiceInterface $statisticService,
        private readonly LogService $logService,
        private readonly PracticeResultRepositoryInterface $practiceResultRepository,
        private readonly StudySessionRepositoryInterface $studySessionRepository,
        private readonly ConsoleRendererInterface $renderer,
    ) {}

    /**
     * List all flashcards for a user.
     */
    public function listFlashcards(User $user): void
    {
        $this->renderer->info('Listing all flashcards...');

        // Log the action
        $this->logService->logFlashcardList($user->id);

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            $this->renderer->warning('You have no flashcards yet.');

            return;
        }

        // Prepare the data for the table
        $headers = ['Question', 'Answer'];
        $rows = [];

        foreach ($flashcards as $flashcard) {
            $rows[] = [
                'Question' => $flashcard->question,
                'Answer' => $flashcard->answer,
            ];
        }

        // Render the flashcards using Laravel Prompts table
        table(
            headers: $headers,
            rows: $rows
        );
    }

    /**
     * Create a new flashcard for a user.
     */
    public function createFlashcard(User $user): void
    {
        $this->renderer->info('Creating a new flashcard...');

        // Get user input for the flashcard
        $question = text(
            label: 'Enter the flashcard question:',
            placeholder: 'What is Laravel?',
            required: true,
            validate: fn (string $value) => mb_strlen($value) < 3
                ? 'The question must be at least 3 characters.'
                : null
        );

        $answer = text(
            label: 'Enter the flashcard answer:',
            placeholder: 'A PHP web application framework',
            required: true,
            validate: fn (string $value) => mb_strlen($value) < 3
                ? 'The answer must be at least 3 characters.'
                : null
        );

        // Review the input
        $this->renderer->info('Question: '.$question);
        $this->renderer->info('Answer: '.$answer);

        // Create the flashcard
        $flashcard = $this->flashcardService->create(
            $user->id,
            [
                'question' => $question,
                'answer' => $answer,
            ]
        );

        if ($flashcard) {
            $this->renderer->success('Flashcard created successfully!');
        } else {
            $this->renderer->error('Failed to create flashcard.');
        }
    }

    /**
     * Delete a flashcard for a user.
     */
    public function deleteFlashcard(User $user): void
    {
        $this->renderer->info('Deleting a flashcard...');

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            $this->renderer->warning('You have no flashcards to delete.');

            return;
        }

        // Prepare the flashcards for selection
        $options = [];
        foreach ($flashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr($flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel deletion';

        // Ask the user to select a flashcard to delete
        $selectedId = select(
            label: 'Select a flashcard to delete:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            $this->renderer->info('Deletion cancelled.');

            return;
        }

        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to delete this flashcard?',
            default: false
        );

        if (! $confirmDelete) {
            $this->renderer->info('Deletion cancelled.');

            return;
        }

        // Delete the flashcard
        $success = $this->flashcardService->delete((int) $selectedId, $user->id);

        if ($success) {
            $this->renderer->success('Flashcard deleted successfully!');
        } else {
            $this->renderer->error('Failed to delete flashcard.');
        }
    }

    /**
     * Show statistics for a user.
     */
    public function showStatistics(User $user): void
    {
        $this->renderer->info('Showing statistics...');

        // Log the action
        $this->logService->logStatisticsView($user->id);

        // Get statistics for the user
        $statistics = $this->statisticService->getStatisticsForUser($user->id);

        if (! $statistics) {
            $this->renderer->warning('No statistics available yet.');

            return;
        }

        // Display the statistics
        $this->renderer->info('Total Flashcards: '.$statistics['flashcards_created']);
        $this->renderer->info('Study Sessions: '.$statistics['study_sessions']);
        $this->renderer->info('Correct Answers: '.$statistics['correct_answers']);
        $this->renderer->info('Incorrect Answers: '.$statistics['incorrect_answers']);

        // Calculate success rate
        $totalAnswers = $statistics['correct_answers'] + $statistics['incorrect_answers'];
        $successRate = 0;
        if ($totalAnswers > 0) {
            $successRate = round(($statistics['correct_answers'] / $totalAnswers) * 100, 2);
        }
        $this->renderer->info('Success Rate: '.$successRate.'%');

        // Get additional statistics
        $averageDuration = $this->statisticService->getAverageStudySessionDuration($user->id);
        $totalStudyTime = $this->statisticService->getTotalStudyTime($user->id);
        $this->renderer->info('Average Study Session Duration: '.$averageDuration.' minutes');
        $this->renderer->info('Total Study Time: '.$totalStudyTime.' minutes');
    }

    /**
     * Reset practice data for a user.
     */
    public function resetPracticeData(User $user): void
    {
        $this->renderer->info('Resetting flashcard data...');

        // Confirm reset
        $confirmReset = confirm(
            label: 'Are you sure you want to reset all practice data? This will delete all practice results and study sessions.',
            default: false
        );

        if (! $confirmReset) {
            $this->renderer->info('Reset cancelled.');

            return;
        }

        // Reset practice data
        try {
            // Delete practice results
            $this->practiceResultRepository->deleteForUser($user->id);

            // Reset statistics
            $statistics = $this->statisticService->getStatisticsForUser($user->id);
            if ($statistics) {
                $statistics['correct_answers'] = 0;
                $statistics['incorrect_answers'] = 0;
                $this->statisticService->updateStatistics($user->id, $statistics);
            }

            // Log the reset
            $this->logService->logPracticeReset($user->id);

            $this->renderer->success('Practice data reset successfully!');
        } catch (Exception $e) {
            $this->renderer->error('Failed to reset practice data: '.$e->getMessage());
        }
    }

    /**
     * View logs for a user.
     */
    public function viewLogs(User $user): void
    {
        try {
            $logs = $this->logService->getLatestActivityForUser($user->id);

            if (empty($logs)) {
                $this->renderer->warning('No activity logs found');

                return;
            }

            foreach ($logs as $log) {
                $this->renderer->info(sprintf(
                    '[%s] %s - %s',
                    $log['level'],
                    $log['action'],
                    $log['details'] ?? ''
                ));
            }
        } catch (Exception $e) {
            $this->renderer->error('An error occurred while fetching logs: '.$e->getMessage());
        }
    }

    /**
     * Access the trash bin for a user.
     */
    public function accessTrashBin(User $user): void
    {
        $this->renderer->info('Accessing trash bin...');

        // Get deleted flashcards
        $deletedFlashcards = $this->flashcardService->getDeletedForUser($user->id);

        if ($deletedFlashcards->isEmpty()) {
            $this->renderer->warning('Your trash bin is empty.');

            return;
        }

        // Show deleted flashcards
        $headers = ['ID', 'Question', 'Answer', 'Deleted At'];
        $rows = [];

        foreach ($deletedFlashcards as $flashcard) {
            $rows[] = [
                'ID' => (string) $flashcard->id,
                'Question' => mb_substr($flashcard->question, 0, 30).(mb_strlen($flashcard->question) > 30 ? '...' : ''),
                'Answer' => mb_substr($flashcard->answer, 0, 30).(mb_strlen($flashcard->answer) > 30 ? '...' : ''),
                'Deleted At' => $flashcard->deleted_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        table(
            headers: $headers,
            rows: $rows
        );

        // Ask what to do with deleted flashcards
        $action = select(
            label: 'What would you like to do?',
            options: [
                'restore' => 'Restore a flashcard',
                'restore-all' => 'Restore all flashcards',
                'delete' => 'Permanently delete a flashcard',
                'delete-all' => 'Permanently delete all flashcards',
                'cancel' => 'Go back',
            ],
            default: 'cancel'
        );

        match ($action) {
            'restore' => $this->restoreFlashcard($user, $deletedFlashcards),
            'restore-all' => $this->restoreAllFlashcards($user),
            'delete' => $this->permanentlyDeleteFlashcard($user, $deletedFlashcards),
            'delete-all' => $this->permanentlyDeleteAllFlashcards($user),
            default => $this->renderer->info('Returning to main menu...'),
        };
    }

    /**
     * Log user exit.
     */
    public function logExit(User $user): void
    {
        $this->logService->logUserExit($user->id);
        $this->renderer->success('See you!');
    }

    /**
     * Practice flashcards for a user.
     *
     * @throws BindingResolutionException
     */
    public function practiceFlashcards(User $user): void
    {
        $this->renderer->info('Starting practice mode...');

        // Get available flashcards for practice
        $flashcards = $this->studySessionRepository->getFlashcardsForPractice($user->id);

        if ($flashcards->isEmpty()) {
            $this->renderer->warning('You have no flashcards to practice.');

            return;
        }

        // Start or get active study session
        $studySession = $this->studySessionRepository->getActiveForUser($user->id);
        if (! $studySession) {
            $studySession = $this->studySessionService->startSession($user->id);
            if (! $studySession) {
                $this->renderer->error('Failed to start study session.');

                return;
            }
        }

        // Prepare tracking variables
        $practiceResults = $this->practiceResultRepository->getForUser($user->id);
        $totalFlashcards = $flashcards->count();
        $correctAnswers = $practiceResults->where('is_correct', true)->count();
        $incorrectAnswers = $practiceResults->where('is_correct', false)->count();
        $notAnswered = $totalFlashcards - $correctAnswers - $incorrectAnswers;
        $practiceComplete = false;

        // Get statistics to show progress
        $statistics = $this->statisticService->getStatisticsForUser($user->id);

        // Main practice loop
        while (! $practiceComplete) {
            // Show current progress
            $progressTable = [
                ['Total Flashcards', (string) $totalFlashcards],
                ['Correct Answers', (string) $correctAnswers],
                ['Incorrect Answers', (string) $incorrectAnswers],
                ['Not Answered', (string) $notAnswered],
                ['Completion', $totalFlashcards > 0 ? round(($correctAnswers / $totalFlashcards) * 100, 2).'%' : '0%'],
            ];

            $this->renderer->table(['Statistic', 'Value'], $progressTable);

            // If all questions are correct, end practice
            if ($correctAnswers === $totalFlashcards) {
                $this->renderer->success('Congratulations! You have correctly answered all flashcards.');
                $practiceComplete = true;

                continue;
            }

            // Prepare question options
            $options = [];
            foreach ($flashcards as $flashcard) {
                // Skip correctly answered flashcards
                $alreadyCorrect = $practiceResults
                    ->where('flashcard_id', $flashcard->id)
                    ->where('is_correct', true)
                    ->isNotEmpty();

                if (! $alreadyCorrect) {
                    $options[$flashcard->id] = mb_substr($flashcard->question, 0, 50);
                }
            }
            $options['exit'] = 'Exit practice mode';

            // If no questions left to answer, end practice
            if (count($options) === 1) { // Only 'exit' option
                $this->renderer->success('Congratulations! You have correctly answered all flashcards.');
                $practiceComplete = true;

                continue;
            }

            // Select a flashcard to practice
            $selectedId = select(
                label: 'Select a flashcard to practice:',
                options: $options,
                default: 'exit'
            );

            if ($selectedId === 'exit') {
                $this->renderer->info('Exiting practice mode...');
                $practiceComplete = true;

                continue;
            }

            // Show the question
            $flashcard = $flashcards->firstWhere('id', $selectedId);
            $this->renderer->info('Question: '.$flashcard->question);

            // Get the user's answer
            $userAnswer = text(
                label: 'Your answer:',
                placeholder: 'Type your answer here',
                required: true
            );

            // Check the answer
            $isCorrect = mb_strtolower(mb_trim($userAnswer)) === mb_strtolower(mb_trim($flashcard->answer));

            // Save the result
            $this->studySessionRepository->recordPracticeResult($user->id, $flashcard->id, $isCorrect);

            // Update tracking variables
            if ($isCorrect) {
                $this->renderer->success('Correct! The answer is: '.$flashcard->answer);
                $correctAnswers++;
                $notAnswered--;
            } else {
                $this->renderer->error('Incorrect. The correct answer is: '.$flashcard->answer);
                $incorrectAnswers++;
                $notAnswered--;
            }

            // Ask if user wants to continue
            $continue = confirm(
                label: 'Continue practicing?',
                default: true
            );

            if (! $continue) {
                $this->renderer->info('Ending practice session...');
                $practiceComplete = true;
            }
        }

        // End the study session if all questions are answered correctly
        if ($correctAnswers === $totalFlashcards) {
            $this->studySessionService->endSession($studySession->id, $user->id);
            $this->renderer->success('Study session completed successfully!');
        }
    }

    /**
     * Register a new user.
     */
    public function registerUser(): User
    {
        $name = text(
            label: 'Enter your user name:',
            placeholder: 'John Doe',
            required: true,
            validate: fn (string $value) => mb_strlen($value) < 3
                ? 'The name must be at least 3 characters.'
                : null,
            transform: fn (string $value) => mb_trim($value)
        );

        $email = text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            required: true,
            validate: ['email' => 'required|email|unique:users,email'],
            transform: fn (string $value) => mb_trim($value)
        );

        $password = text(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: fn (string $value) => mb_strlen($value) < 8
                ? 'The password must be at least 8 characters.'
                : null,
            transform: fn (string $value) => mb_trim($value)
        );

        // Create a new user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->renderer->success("User {$user->name} registered successfully with email {$user->email}.");

        return $user;
    }

    /**
     * Import flashcards from a CSV file for a user.
     *
     * @param  int  $userId  The ID of the user to import flashcards for
     * @param  string  $filePath  The path to the CSV file
     * @return bool True if the import was successful, false otherwise
     */
    public function importFlashcardsFromFile(int $userId, string $filePath): bool
    {
        try {
            // Check if file exists
            if (! file_exists($filePath)) {
                $this->renderer->error("File not found: {$filePath}");

                return false;
            }

            // Check if user exists
            $user = User::find($userId);
            if (! $user) {
                $this->renderer->error("User not found with ID: {$userId}");

                return false;
            }

            // Open file and read contents
            $file = fopen($filePath, 'r');
            if (! $file) {
                $this->renderer->error("Could not open file: {$filePath}");

                return false;
            }

            // Read header row
            $header = fgetcsv($file);
            if (! $header || count($header) < 2) {
                $this->renderer->error('Invalid CSV format. Expected at least 2 columns (question, answer).');
                fclose($file);

                return false;
            }

            // Find the column indices for question and answer
            $questionIndex = array_search('question', array_map('strtolower', $header));
            $answerIndex = array_search('answer', array_map('strtolower', $header));

            if ($questionIndex === false || $answerIndex === false) {
                $this->renderer->error("CSV must contain 'question' and 'answer' columns.");
                fclose($file);

                return false;
            }

            // Read and import flashcards
            $importCount = 0;
            $rowNumber = 1; // Start at 1 because we already read the header row
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty($row) || count($row) <= max($questionIndex, $answerIndex)) {
                    $this->renderer->warning("Skipping row {$rowNumber}: Insufficient columns.");

                    continue;
                }

                $question = mb_trim($row[$questionIndex]);
                $answer = mb_trim($row[$answerIndex]);

                // Validate data
                if (empty($question) || empty($answer)) {
                    $this->renderer->warning("Skipping row {$rowNumber}: Empty question or answer.");

                    continue;
                }

                // Create flashcard
                $flashcard = $this->flashcardService->create($userId, [
                    'question' => $question,
                    'answer' => $answer,
                ]);

                if ($flashcard) {
                    $importCount++;
                } else {
                    $this->renderer->warning("Failed to create flashcard at row {$rowNumber}.");
                }
            }

            fclose($file);

            // Log the import
            $this->logService->logFlashcardImport($userId, $importCount);

            $this->renderer->success("Successfully imported {$importCount} flashcards for user ID {$userId}.");

            return true;
        } catch (Exception $e) {
            $this->renderer->error('Error importing flashcards: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Restore a flashcard.
     */
    private function restoreFlashcard(User $user, $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        foreach ($deletedFlashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr($flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel';

        // Select flashcard to restore
        $selectedId = select(
            label: 'Select a flashcard to restore:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            $this->renderer->info('Restore cancelled.');

            return;
        }

        // Restore the flashcard
        $success = $this->flashcardService->restore((int) $selectedId, $user->id);

        if ($success) {
            $this->renderer->success('Flashcard restored successfully!');
        } else {
            $this->renderer->error('Failed to restore flashcard.');
        }
    }

    /**
     * Restore all flashcards.
     */
    private function restoreAllFlashcards(User $user): void
    {
        // Confirm restoration
        $confirmRestore = confirm(
            label: 'Are you sure you want to restore all deleted flashcards?',
            default: true
        );

        if (! $confirmRestore) {
            $this->renderer->info('Restore cancelled.');

            return;
        }

        // Restore all flashcards
        $success = $this->flashcardService->restoreAllForUser($user->id);

        if ($success) {
            // Log the action
            $this->logService->logAllFlashcardsRestore($user->id);
            $this->renderer->success('All flashcards restored successfully!');
        } else {
            $this->renderer->error('Failed to restore flashcards.');
        }
    }

    /**
     * Permanently delete a flashcard.
     */
    private function permanentlyDeleteFlashcard(User $user, $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        foreach ($deletedFlashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr($flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel';

        // Select flashcard to delete
        $selectedId = select(
            label: 'Select a flashcard to permanently delete:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            $this->renderer->info('Deletion cancelled.');

            return;
        }

        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to permanently delete this flashcard? This action cannot be undone.',
            default: false
        );

        if (! $confirmDelete) {
            $this->renderer->info('Deletion cancelled.');

            return;
        }

        // Delete the flashcard
        $success = $this->flashcardService->forceDelete((int) $selectedId, $user->id);

        if ($success) {
            $this->renderer->success('Flashcard permanently deleted!');
        } else {
            $this->renderer->error('Failed to delete flashcard.');
        }
    }

    /**
     * Permanently delete all flashcards.
     */
    private function permanentlyDeleteAllFlashcards(User $user): void
    {
        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to permanently delete ALL trashed flashcards? This action cannot be undone.',
            default: false
        );

        if (! $confirmDelete) {
            $this->renderer->info('Deletion cancelled.');

            return;
        }

        // Delete all flashcards
        $success = $this->flashcardService->forceDeleteAllForUser($user->id);

        if ($success) {
            // Log the action
            $this->logService->logAllFlashcardsPermanentDelete($user->id);
            $this->renderer->success('All flashcards permanently deleted!');
        } else {
            $this->renderer->error('Failed to delete flashcards.');
        }
    }
}
