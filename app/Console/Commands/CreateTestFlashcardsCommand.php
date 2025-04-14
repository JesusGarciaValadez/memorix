<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\StudySessionService;

final class CreateTestFlashcardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:create-test {email=test@example.com} {password=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test flashcards and practice data for a user';

    /**
     * Execute the console command.
     */
    public function handle(
        FlashcardService $flashcardService,
        StudySessionService $studySessionService,
        StatisticRepositoryInterface $statisticRepository
    ): void {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Find or create the user
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("User created with email: {$email}");
        } else {
            $this->info("Using existing user: {$user->name}");
        }

        // Create some test flashcards
        $flashcards = [
            [
                'question' => 'What is Laravel?',
                'answer' => 'Laravel is a PHP web application framework with expressive, elegant syntax.',
            ],
            [
                'question' => 'What is Eloquent?',
                'answer' => 'Eloquent is Laravel\'s active record implementation for working with your database.',
            ],
            [
                'question' => 'What is Blade?',
                'answer' => 'Blade is the simple, yet powerful templating engine provided with Laravel.',
            ],
            [
                'question' => 'What is Artisan?',
                'answer' => 'Artisan is the command-line interface included with Laravel.',
            ],
            [
                'question' => 'What is Composer?',
                'answer' => 'Composer is a dependency manager for PHP.',
            ],
        ];

        $createdFlashcards = [];
        foreach ($flashcards as $flashcardData) {
            // Only create if it doesn't exist already
            $existing = $flashcardService->getAllForUser($user->id, 100)
                ->getCollection()
                ->firstWhere('question', $flashcardData['question']);

            if (! $existing) {
                $flashcard = $flashcardService->create($user->id, $flashcardData);
                $createdFlashcards[] = $flashcard;
                $this->info("Created flashcard: {$flashcard->question}");
            } else {
                $createdFlashcards[] = $existing;
                $this->info("Flashcard already exists: {$existing->question}");
            }
        }

        // Create a study session and record some practice results
        $this->info('Simulating practice session...');

        // Start a study session
        $studySession = $studySessionService->startSession($user->id);

        // Record correct answers for some flashcards
        foreach ($createdFlashcards as $index => $flashcard) {
            // Make some correct and some incorrect to create statistics
            $isCorrect = $index % 3 !== 0; // 2/3 correct, 1/3 incorrect

            $studySessionService->recordPracticeResult(
                $user->id,
                $flashcard->id,
                $isCorrect
            );

            $this->info('Recorded '.($isCorrect ? 'correct' : 'incorrect').' answer for: '.$flashcard->question);
        }

        // End the study session
        $studySessionService->endSession($user->id, $studySession->id);

        // Report statistics
        $stats = $statisticRepository->getForUser($user->id);

        $this->info('');
        $this->info('Test data created successfully with the following statistics:');
        $this->info("Total Flashcards: {$stats->total_flashcards}");
        $this->info("Study Sessions: {$stats->total_study_sessions}");
        $this->info("Correct Answers: {$stats->total_correct_answers}");
        $this->info("Incorrect Answers: {$stats->total_incorrect_answers}");
        $this->info("Completion: {$stats->getCompletionPercentage()}%");
        $this->info("Success Rate: {$stats->getCorrectPercentage()}%");

        $this->info('');
        $this->info("You can now run: sail artisan flashcard:interactive {$email} {$password} --statistics");
    }
}
