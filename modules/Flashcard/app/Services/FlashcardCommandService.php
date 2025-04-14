<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final readonly class FlashcardCommandService implements FlashcardCommandServiceInterface
{
    public function __construct(
        private FlashcardService $flashcardService,
        private StudySessionService $studySessionService,
        private StatisticServiceInterface $statisticService,
        private LogService $logService,
        private PracticeResultRepositoryInterface $practiceResultRepository,
        private StudySessionRepositoryInterface $studySessionRepository,
    ) {}

    /**
     * List all flashcards for a user.
     */
    public function listFlashcards(User $user, FlashcardInteractiveCommand $command): void
    {
        note('Listing all flashcards...');

        // Log the action
        $this->logService->logFlashcardList($user->id);

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            warning('You have no flashcards yet.');

            $command->shouldKeepRunning = false;
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
        info('Creating a new flashcard...');

        // Get user input for the flashcard
        $question = text(
            label: 'Enter the flashcard question:',
            placeholder: 'What is Laravel?',
            required: true,
            validate: fn (string $value): ?string => mb_strlen($value) < 3
                ? 'The question must be at least 3 characters.'
                : null
        );

        $answer = text(
            label: 'Enter the flashcard answer:',
            placeholder: 'A PHP web application framework',
            required: true,
            validate: fn (string $value): ?string => mb_strlen($value) < 3
                ? 'The answer must be at least 3 characters.'
                : null
        );

        // Review the input
        info('Question: '.$question);
        info('Answer: '.$answer);

        // Create the flashcard
        $flashcard = $this->flashcardService->create(
            $user->id,
            [
                'question' => $question,
                'answer' => $answer,
            ]
        );

        if ($flashcard) {
            note('Flashcard created successfully!');
        } else {
            error('Failed to create flashcard.');
        }
    }

    /**
     * Delete a flashcard for a user.
     */
    public function deleteFlashcard(User $user): void
    {
        info('Deleting a flashcard...');

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            warning('You have no flashcards to delete.');

            return;
        }

        // Prepare the flashcards for selection
        $options = [];
        foreach ($flashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr((string) $flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel deletion';

        // Ask the user to select a flashcard to delete
        $selectedId = select(
            label: 'Select a flashcard to delete:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            info('Deletion cancelled.');

            return;
        }

        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to delete this flashcard?',
            default: false
        );

        if (! $confirmDelete) {
            info('Deletion cancelled.');

            return;
        }

        // Delete the flashcard
        $success = $this->flashcardService->delete((int) $selectedId, $user->id);

        if ($success) {
            note('Flashcard deleted successfully!');
        } else {
            error('Failed to delete flashcard.');
        }
    }

    /**
     * Show statistics for a user.
     */
    public function showStatistics(User $user): void
    {
        info('Showing statistics...');

        // Log the action
        $this->logService->logStatisticsView($user->id);

        // Get statistics for the user
        $statistics = $this->statisticService->getStatisticsForUser($user->id);

        if ($statistics === []) {
            warning('No statistics available yet.');

            return;
        }

        // Display the statistics
        info('Total Flashcards: '.$statistics['flashcards_created']);
        info('Study Sessions: '.$statistics['study_sessions']);
        info('Correct Answers: '.$statistics['correct_answers']);
        info('Incorrect Answers: '.$statistics['incorrect_answers']);

        // Calculate success rate
        $totalAnswers = $statistics['correct_answers'] + $statistics['incorrect_answers'];
        $successRate = 0;
        if ($totalAnswers > 0) {
            $successRate = round(($statistics['correct_answers'] / $totalAnswers) * 100, 2);
        }
        info('Success Rate: '.$successRate.'%');

        // Get additional statistics
        $averageDuration = $this->statisticService->getAverageStudySessionDuration($user->id);
        $totalStudyTime = $this->statisticService->getTotalStudyTime($user->id);
        info('Average Study Session Duration: '.$averageDuration.' minutes');
        info('Total Study Time: '.$totalStudyTime.' minutes');
    }

    /**
     * Reset practice data for a user.
     */
    public function resetPracticeData(User $user): void
    {
        info('Resetting flashcard data...');

        // Confirm reset
        $confirmReset = confirm(
            label: 'Are you sure you want to reset all practice data? This will delete all practice results and study sessions.',
            default: false
        );

        if (! $confirmReset) {
            info('Reset cancelled.');

            return;
        }

        // Reset practice data
        try {
            // Delete practice results
            $this->practiceResultRepository->deleteForUser($user->id);

            // Reset statistics
            $statistics = $this->statisticService->getStatisticsForUser($user->id);
            if ($statistics !== []) {
                $statistics['correct_answers'] = 0;
                $statistics['incorrect_answers'] = 0;
                $this->statisticService->updateStatistics($user->id, $statistics);
            }

            // Log the reset
            $this->logService->logPracticeReset($user->id);

            note('Practice data reset successfully!');
        } catch (Exception $e) {
            error('Failed to reset practice data: '.$e->getMessage());
        }
    }

    /**
     * View logs for a user.
     */
    public function viewLogs(User $user): void
    {
        try {
            $logs = $this->logService->getLatestActivityForUser($user->id);

            if ($logs === []) {
                warning('No activity logs found');

                return;
            }

            foreach ($logs as $log) {
                info(sprintf(
                    '[%s] %s - %s',
                    $log['level'],
                    $log['action'],
                    $log['details'] ?? ''
                ));
            }
        } catch (Exception $e) {
            error('An error occurred while fetching logs: '.$e->getMessage());
        }
    }

    /**
     * Access the trash bin for a user.
     */
    public function accessTrashBin(User $user): void
    {
        info('Accessing trash bin...');

        // Get deleted flashcards
        $deletedFlashcards = $this->flashcardService->getDeletedForUser($user->id);

        if ($deletedFlashcards->isEmpty()) {
            warning('Your trash bin is empty.');

            return;
        }

        // Show deleted flashcards
        $headers = ['ID', 'Question', 'Answer', 'Deleted At'];
        $rows = [];

        foreach ($deletedFlashcards as $flashcard) {
            $rows[] = [
                'ID' => (string) $flashcard->id,
                'Question' => mb_substr((string) $flashcard->question, 0, 30).(mb_strlen((string) $flashcard->question) > 30 ? '...' : ''),
                'Answer' => mb_substr((string) $flashcard->answer, 0, 30).(mb_strlen((string) $flashcard->answer) > 30 ? '...' : ''),
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
            default => info('Returning to main menu...'),
        };
    }

    /**
     * Log user exit.
     */
    public function logExit(User $user): void
    {
        $this->logService->logUserExit($user->id);
        note('See you!');
    }

    /**
     * Practice flashcards for a user.
     *
     * @throws BindingResolutionException
     */
    public function practiceFlashcards(User $user): void
    {
        info('Starting practice mode...');

        // Get available flashcards for practice
        $flashcards = $this->studySessionRepository->getFlashcardsForPractice($user->id);

        if ($flashcards->isEmpty()) {
            warning('You have no flashcards to practice.');

            return;
        }

        // Start or get active study session
        $studySession = $this->studySessionRepository->getActiveForUser($user->id);
        if (! $studySession) {
            $studySession = $this->studySessionService->startSession($user->id);
            if (! $studySession) {
                error('Failed to start study session.');

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
        $this->statisticService->getStatisticsForUser($user->id);

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

            table(['Statistic', 'Value'], $progressTable);

            // If all questions are correct, end practice
            if ($correctAnswers === $totalFlashcards) {
                note('Congratulations! You have correctly answered all flashcards.');
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
                    $options[$flashcard->id] = mb_substr((string) $flashcard->question, 0, 50);
                }
            }
            $options['exit'] = 'Exit practice mode';

            // If no questions left to answer, end practice
            if (count($options) === 1) { // Only 'exit' option
                note('Congratulations! You have correctly answered all flashcards.');
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
                info('Exiting practice mode...');
                $practiceComplete = true;

                continue;
            }

            // Show the question
            $flashcard = $flashcards->firstWhere('id', $selectedId);
            info('Question: '.$flashcard->question);

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
                note('Correct! The answer is: '.$flashcard->answer);
                $correctAnswers++;
                $notAnswered--;
            } else {
                error('Incorrect. The correct answer is: '.$flashcard->answer);
                $incorrectAnswers++;
                $notAnswered--;
            }

            // Ask if user wants to continue
            $continue = confirm(
                label: 'Continue practicing?',
                default: true
            );

            if (! $continue) {
                info('Ending practice session...');
                $practiceComplete = true;
            }
        }

        // End the study session if all questions are answered correctly
        if ($correctAnswers === $totalFlashcards) {
            $this->studySessionService->endSession($studySession->id, $user->id);
            note('Study session completed successfully!');
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
            validate: fn (string $value): ?string => mb_strlen($value) < 3
                ? 'The name must be at least 3 characters.'
                : null,
            transform: fn (string $value): string => mb_trim($value)
        );

        $email = text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            required: true,
            validate: ['email' => 'required|email|unique:users,email'],
            transform: fn (string $value): string => mb_trim($value)
        );

        $password = text(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: fn (string $value): ?string => mb_strlen($value) < 8
                ? 'The password must be at least 8 characters.'
                : null,
            transform: fn (string $value): string => mb_trim($value)
        );

        // Create a new user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        note("User {$user->name} registered successfully with email {$user->email}.");

        return $user;
    }

    public function importFlashcardsFromFile(int $userId, string $filePath): bool
    {
        try {
            // Check if file exists
            if (! file_exists($filePath)) {
                error("File not found: {$filePath}");

                return false;
            }

            // Check if user exists
            $user = User::find($userId);
            if (! $user) {
                error("User not found with ID: {$userId}");

                return false;
            }

            // Open file and read contents
            $file = fopen($filePath, 'r');
            if (! $file) {
                error("Could not open file: {$filePath}");

                return false;
            }

            // Read header row
            $header = fgetcsv($file, escape: '\\');
            if ($header === [] || $header === false || count($header) < 2) {
                error('Invalid CSV format. Expected at least 2 columns (question, answer).');
                fclose($file);

                return false;
            }

            // Find the column indices for question and answer
            $questionIndex = in_array('question', array_map('strtolower', $header), true);
            $answerIndex = in_array('answer', array_map('strtolower', $header), true);

            if ($questionIndex === false || $answerIndex === false) {
                error("CSV must contain 'question' and 'answer' columns.");
                fclose($file);

                return false;
            }

            // Read and import flashcards
            $importCount = 0;
            $rowNumber = 1; // Start at 1 because we already read the header row
            while (($row = fgetcsv($file, escape: '\\')) !== false) {
                $rowNumber++;

                // Skip empty rows
                if ($row === [] || count($row) <= max($questionIndex, $answerIndex)) {
                    warning("Skipping row {$rowNumber}: Insufficient columns.");

                    continue;
                }

                $question = mb_trim($row[$questionIndex]);
                $answer = mb_trim($row[$answerIndex]);

                // Validate data
                if ($question === '' || $question === '0' || ($answer === '' || $answer === '0')) {
                    warning("Skipping row {$rowNumber}: Empty question or answer.");

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
                    warning("Failed to create flashcard at row {$rowNumber}.");
                }
            }

            fclose($file);

            // Log the import
            $this->logService->logFlashcardImport($userId, $importCount);

            note("Successfully imported {$importCount} flashcards for user ID {$userId}.");

            return true;
        } catch (Exception $e) {
            error('Error importing flashcards: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Restore a flashcard.
     */
    private function restoreFlashcard(User $user, \Illuminate\Contracts\Pagination\LengthAwarePaginator $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        foreach ($deletedFlashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr((string) $flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel';

        // Select flashcard to restore
        $selectedId = select(
            label: 'Select a flashcard to restore:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            info('Restore cancelled.');

            return;
        }

        // Restore the flashcard
        $success = $this->flashcardService->restore((int) $selectedId, $user->id);

        if ($success) {
            note('Flashcard restored successfully!');
        } else {
            error('Failed to restore flashcard.');
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
            info('Restore cancelled.');

            return;
        }

        // Restore all flashcards
        $success = $this->flashcardService->restoreAllForUser($user->id);

        if ($success) {
            // Log the action
            $this->logService->logAllFlashcardsRestore($user->id);
            note('All flashcards restored successfully!');
        } else {
            error('Failed to restore flashcards.');
        }
    }

    /**
     * Permanently delete a flashcard.
     */
    private function permanentlyDeleteFlashcard(User $user, \Illuminate\Contracts\Pagination\LengthAwarePaginator $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        foreach ($deletedFlashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr((string) $flashcard->question, 0, 50);
        }
        $options['cancel'] = 'Cancel';

        // Select flashcard to delete
        $selectedId = select(
            label: 'Select a flashcard to permanently delete:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedId === 'cancel') {
            info('Deletion cancelled.');

            return;
        }

        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to permanently delete this flashcard? This action cannot be undone.',
            default: false
        );

        if (! $confirmDelete) {
            info('Deletion cancelled.');

            return;
        }

        // Delete the flashcard
        $success = $this->flashcardService->forceDelete((int) $selectedId, $user->id);

        if ($success) {
            note('Flashcard permanently deleted!');
        } else {
            error('Failed to delete flashcard.');
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
            info('Deletion cancelled.');

            return;
        }

        // Delete all flashcards
        $success = $this->flashcardService->forceDeleteAllForUser($user->id);

        if ($success) {
            // Log the action
            $this->logService->logAllFlashcardsPermanentDelete($user->id);
            note('All flashcards permanently deleted!');
        } else {
            error('Failed to delete flashcards.');
        }
    }
}
