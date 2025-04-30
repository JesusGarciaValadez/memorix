<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final readonly class FlashcardCommandService implements FlashcardCommandServiceInterface
{
    public function __construct(
        private FlashcardServiceInterface $flashcardService,
        private StudySessionServiceInterface $studySessionService,
        private StatisticServiceInterface $statisticService,
        private LogServiceInterface $logService,
        private PracticeResultRepositoryInterface $practiceResultRepository,
        private StudySessionRepositoryInterface $studySessionRepository,
    ) {}

    /**
     * List all flashcards for a user.
     */
    public function listFlashcards(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Listing all flashcards...');

        // Log the action
        $this->logService->logFlashcardList($user->id);

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            $command->warn('You have no flashcards yet.');

            $command->shouldKeepRunning = false;

            return;
        }

        // Prepare the data for the table
        $headers = ['Question', 'Answer'];
        $rows = [];

        /** @var Flashcard $flashcard */
        foreach ($flashcards as $flashcard) {
            $rows[] = [
                'Question' => $flashcard->question,
                'Answer' => $flashcard->answer,
            ];
        }

        // Render the flashcards using Laravel Prompts table
        $command->table(
            headers: $headers,
            rows: $rows
        );
    }

    /**
     * Create a new flashcard for a user.
     */
    public function createFlashcard(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Creating a new flashcard...');

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
        $command->info('Question: '.$question);
        $command->info('Answer: '.$answer);

        // Create the flashcard
        $flashcard = $this->flashcardService->create(
            $user->id,
            [
                'question' => $question,
                'answer' => $answer,
            ]
        );

        if ($flashcard::exists()) {
            $command->info('Flashcard created successfully!');
        } else {
            $command->error('Failed to create flashcard.');
        }
    }

    /**
     * Delete a flashcard for a user.
     */
    public function deleteFlashcard(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Deleting a flashcard...');

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            $command->warn('You have no flashcards to delete.');

            return;
        }

        // Prepare the flashcards for selection
        $options = [];
        /** @var Flashcard $flashcard */
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
            $command->info('Deletion cancelled.');

            return;
        }

        // Confirm deletion
        $confirmDelete = confirm(
            label: 'Are you sure you want to delete this flashcard?',
            default: false
        );

        if (! $confirmDelete) {
            $command->info('Deletion cancelled.');

            return;
        }

        // Delete the flashcard
        $success = $this->flashcardService->delete((int) $selectedId, $user->id);

        if ($success) {
            $command->info('Flashcard deleted successfully!');
        } else {
            $command->error('Failed to delete flashcard.');
        }
    }

    /**
     * Show statistics for a user.
     */
    public function showStatistics(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Showing statistics...');

        // Log the action
        $this->logService->logStatisticsView($user->id);

        // Get statistics for the user
        $statistics = $this->statisticService->getStatisticsForUser($user->id);

        if ($statistics === []) {
            $command->warn('No statistics available yet.');

            return;
        }

        // Display the statistics
        $command->info('Total Flashcards: '.$statistics['flashcards_created']);
        $command->info('Study Sessions: '.$statistics['study_sessions']);
        $command->info('Correct Answers: '.$statistics['correct_answers']);
        $command->info('Incorrect Answers: '.$statistics['incorrect_answers']);

        // Calculate success rate
        $correct = $statistics['correct_answers'];
        $incorrect = $statistics['incorrect_answers'];
        $totalAnswers = $correct + $incorrect;
        $successRate = 0;
        if ($totalAnswers > 0) {
            $successRate = round(($correct / $totalAnswers) * 100, 2);
        }
        $command->info('Success Rate: '.$successRate.'%');

        // Get additional statistics
        $averageDuration = $this->statisticService->getAverageStudySessionDuration($user->id);
        $totalStudyTime = $this->statisticService->getTotalStudyTime($user->id);
        $command->info('Average Study Session Duration: '.$averageDuration.' minutes');
        $command->info('Total Study Time: '.$totalStudyTime.' minutes');
    }

    /**
     * Reset practice data for a user.
     */
    public function resetPracticeData(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Resetting flashcard data...');

        // Confirm reset
        $confirmReset = confirm(
            label: 'Are you sure you want to reset all practice data? This will delete all practice results and study sessions.',
            default: false
        );

        if (! $confirmReset) {
            $command->info('Reset cancelled.');

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

            $command->info('Practice data reset successfully!');
        } catch (Exception $e) {
            $command->error('Failed to reset practice data: '.$e->getMessage());
        }
    }

    /**
     * View logs for a user.
     */
    public function viewLogs(User $user, FlashcardInteractiveCommand $command): void
    {
        try {
            $logs = $this->logService->getLatestActivityForUser($user->id);

            if ($logs === []) {
                $command->warn('No activity logs found');

                return;
            }

            foreach ($logs as $log) {
                $command->info(sprintf(
                    '[%s] %s - %s',
                    (string) $log['level'],
                    (string) $log['action'],
                    (string) ($log['details'] ?? '')
                ));
            }
        } catch (Exception $e) {
            $command->error('An error occurred while fetching logs: '.$e->getMessage());
        }
    }

    /**
     * Access the trash bin for a user.
     */
    public function accessTrashBin(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Accessing trash bin...');

        // Get deleted flashcards
        $deletedFlashcards = $this->flashcardService->getDeletedForUser($user->id);

        if ($deletedFlashcards->isEmpty()) {
            $command->warn('Your trash bin is empty.');

            return;
        }

        // Show deleted flashcards
        $headers = ['ID', 'Question', 'Answer', 'Deleted At'];
        $rows = [];

        /** @var Flashcard $flashcard */
        foreach ($deletedFlashcards->items() as $flashcard) {
            $rows[] = [
                'ID' => (string) $flashcard->id,
                'Question' => mb_substr((string) $flashcard->question, 0, 30).(mb_strlen((string) $flashcard->question) > 30 ? '...' : ''),
                'Answer' => mb_substr((string) $flashcard->answer, 0, 30).(mb_strlen((string) $flashcard->answer) > 30 ? '...' : ''),
                // @phpstan-ignore-next-line class.notFound
                'Deleted At' => $flashcard->deleted_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        $command->table(
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
    public function logExit(User $user, FlashcardInteractiveCommand $command): void
    {
        $this->logService->logUserExit($user->id);
        $command->info('See you!');
    }

    /**
     * Practice flashcards for a user.
     */
    public function practiceFlashcards(User $user, FlashcardInteractiveCommand $command): void
    {
        $command->info('Starting practice mode...');

        // Get available flashcards for practice
        /** @var EloquentCollection<int, Flashcard> $flashcards */
        $flashcards = Flashcard::hydrate($this->studySessionRepository->getFlashcardsForPractice($user->id));

        if ($flashcards->isEmpty()) {
            $command->warn('You have no flashcards to practice.');

            return;
        }

        // Start or get active study session
        $studySession = $this->studySessionRepository->getActiveSessionForUser($user->id);
        if (! $studySession instanceof \Modules\Flashcard\app\Models\StudySession) {
            $studySession = $this->studySessionService->startSession($user->id);
            if (! $studySession) {
                $command->error('Failed to start study session.');

                return;
            }
        }

        // Prepare tracking variables
        /** @var EloquentCollection<int, \Modules\Flashcard\app\Models\PracticeResult> $practiceResults */
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
                ['Completion', $totalFlashcards > 0 ? round((int) $correctAnswers / (int) $totalFlashcards * 100, 2).'%' : '0%'],
            ];

            $command->table(['Statistic', 'Value'], $progressTable);

            // If all questions are correct, end practice
            if ($correctAnswers === $totalFlashcards) {
                $command->info('Congratulations! You have correctly answered all flashcards.');
                $practiceComplete = true;

                continue;
            }

            // Prepare question options
            $options = [];
            /** @var Flashcard $flashcard */
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
                $command->info('Congratulations! You have correctly answered all flashcards.');
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
                $command->info('Exiting practice mode...');
                $practiceComplete = true;
                $command->shouldKeepRunning = false;

                continue;
            }

            // Show the question
            $selectedFlashcardById = Flashcard::find($selectedId);

            if (! $selectedFlashcardById) {
                $command->error("Flashcard with ID {$selectedId} not found. Skipping.");

                continue; // Skip to the next iteration
            }

            // Now find the corresponding flashcard in the current practice collection
            $flashcard = $flashcards->firstWhere('id', $selectedFlashcardById->id);

            // It's theoretically possible the flashcard found by ID isn't in the $flashcards collection
            // if the collection logic is flawed, though unlikely with current setup. Better safe than sorry.
            if (! $flashcard) {
                $command->error("Flashcard with ID {$selectedId} found in DB but not in current practice set. Skipping.");

                continue; // Skip to the next iteration
            }

            $command->info('Question: '.$flashcard->question);

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
                $command->info('Correct! The answer is: '.$flashcard->answer);
                $correctAnswers++;
            } else {
                $command->error('Incorrect. The correct answer is: '.$flashcard->answer);
                $incorrectAnswers++;
            }
            $notAnswered--;

            // Ask if user wants to continue
            $continue = confirm(
                label: 'Continue practicing?',
                default: true
            );

            if (! $continue) {
                $command->info('Ending practice session...');
                $practiceComplete = true;
            }
        }

        // End the study session if all questions are answered correctly
        if ($correctAnswers === $totalFlashcards) {
            $this->studySessionService->endSession($studySession->id, $user->id);
            $command->info('Study session completed successfully!');
        }
    }

    /**
     * Register a new user.
     */
    public function registerUser(Command $command): User
    {
        $name = text(
            label: 'Enter your user name:',
            placeholder: 'John Doe',
            required: true,
            validate: ['name' => 'required|string|min:3'],
            transform: fn (string $value): string => mb_trim($value)
        );

        $email = text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            required: true,
            validate: ['email' => 'required|email|unique:users,email|max:250'],
            transform: fn (string $value): string => mb_trim($value)
        );

        $password = text(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: [
                'password' => [
                    'required',
                    'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[\W_]).{8,}$/i',
                    'string',
                    'min:8',
                    'max:250',
                ],
            ],
            transform: fn (string $value): string => mb_trim($value)
        );

        // Create a new user
        $user = User::create([
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $command->info(
            sprintf(
                'User %s registered successfully with email %s.',
                $user->name,
                $user->email
            )
        );

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
            // @phpstan-ignore-next-line booleanNot.alwaysFalse
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

            // Find the keys (indices) of 'question' and 'answer' in the lowercased header
            // Ensure all header elements are strings before lowercasing
            $headerLower = array_map(fn ($h) => is_string($h) ? mb_strtolower($h) : '', $header);
            $questionIndex = array_search('question', $headerLower, true);
            $answerIndex = array_search('answer', $headerLower, true);

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
                if ($row === [] || count($row) <= max((int) $questionIndex, (int) $answerIndex)) {
                    warning("Skipping row {$rowNumber}: Insufficient columns.");

                    continue;
                }

                // Ensure value is string before trim
                $questionRaw = $row[$questionIndex];
                $answerRaw = $row[$answerIndex];
                $question = is_string($questionRaw) ? mb_trim($questionRaw) : '';
                $answer = is_string($answerRaw) ? mb_trim($answerRaw) : '';

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

                // @phpstan-ignore-next-line if.alwaysTrue
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
     *
     * @param  LengthAwarePaginator<int, Flashcard>  $deletedFlashcards
     */
    private function restoreFlashcard(User $user, LengthAwarePaginator $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        /** @var Flashcard $flashcard */
        foreach ($deletedFlashcards->items() as $flashcard) {
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
     *
     * @param  LengthAwarePaginator<int, Flashcard>  $deletedFlashcards
     */
    private function permanentlyDeleteFlashcard(User $user, LengthAwarePaginator $deletedFlashcards): void
    {
        // Prepare options for selection
        $options = [];
        /** @var Flashcard $flashcard */
        foreach ($deletedFlashcards->items() as $flashcard) {
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
