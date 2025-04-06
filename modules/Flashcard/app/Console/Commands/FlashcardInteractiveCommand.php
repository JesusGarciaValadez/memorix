<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use AllowDynamicProperties;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Termwind\render;

#[AllowDynamicProperties]
final class FlashcardInteractiveCommand extends Command implements Isolatable
{
    protected $signature = 'flashcard:interactive
        {email? : The email of the user}
        {password? : The password of the user}
        {--list : List all flashcards}
        {--create : Create a new flashcard}
        {--delete : Delete a flashcard}
        {--practice : Practice study mode}
        {--statistics : Show statistics}
        {--reset : Reset the flashcards data}
        {--register : Register a new user}
    ';

    protected $description = 'Display a main menu of available Flashcard options';

    protected bool $shouldKeepRunning = true;

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->trap([SIGTERM, SIGABRT, SIGQUIT], fn () => $this->shouldKeepRunning = false);

        // Handle primary command options first
        if ($this->option('register')) {
            $this->call('flashcard:register');

            return;
        }

        // Get email and password
        $email = $this->argument('email') ?? text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            required: true,
            validate: ['email' => 'required|email|exists:users,email'],
            transform: fn (string $value) => mb_trim($value)
        );

        $password = $this->argument('password') ?? password(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: fn (string $value) => match (true) {
                ! Hash::check(
                    $value,
                    $this->userRepository->getPasswordByEmail($email)
                ) => 'Invalid password. Please try again.',
                default => null,
            },
            transform: fn (string $value) => mb_trim($value)
        );

        $user = $this->userRepository->findByEmail($email);

        render('<p class="p-3 bg-green-600 text-white font-bold">Hi '.$user->name.', welcome to your flashcards</p>');

        // Check for direct action options
        if ($this->option('list')) {
            $this->listFlashcards();

            return;
        }

        if ($this->option('create')) {
            $this->createFlashcard();

            return;
        }

        if ($this->option('delete')) {
            $this->deleteFlashcard();

            return;
        }

        if ($this->option('practice')) {
            $this->editFlashcard();

            return;
        }

        if ($this->option('statistics')) {
            $this->statisticsFlashcards();

            return;
        }

        if ($this->option('reset')) {
            $this->resetFlashcard();

            return;
        }

        // If no specific action option was provided, enter the interactive menu
        while ($this->shouldKeepRunning) {
            $option = $this->select();

            // Handle the command logic here based on selected option
            switch ($option) {
                case 'list':
                    $this->listFlashcards();
                    break;
                case 'create':
                    $this->createFlashcard();
                    break;
                case 'delete':
                    $this->deleteFlashcard();
                    break;
                case 'practice':
                    $this->editFlashcard();
                    break;
                case 'statistics':
                    $this->statisticsFlashcards();
                    break;
                case 'reset':
                    $this->resetFlashcard();
                    break;
                case 'exit':
                    $this->exitCommand();
                    break;
            }
        }
    }

    /**
     * Get the isolatable ID for the command.
     */
    public function isolatableId(): string
    {
        return $this->argument('email');
    }

    /**
     * Display a select menu for flashcard options.
     *
     * This method can be mocked in tests.
     */
    protected function select(): string
    {
        return select(
            label: 'Please, select an option:',
            options: [
                'list' => 'List Flashcards',
                'create' => 'Create Flashcard',
                'delete' => 'Delete Flashcard',
                'practice' => 'Practice Study Mode',
                'statistics' => 'Statistics',
                'reset' => 'Reset the flashcards data',
                'exit' => 'Exit',
            ],
            default: 'practice',
            scroll: 8,
            hint: 'Use arrow keys to navigate and press Enter to select an option.',
        );
    }

    /**
     * List all flashcards.
     */
    protected function listFlashcards(): void
    {
        $this->info('Listing all flashcards...');
        // Implementation will be added later
    }

    /**
     * Create a new flashcard.
     */
    protected function createFlashcard(): void
    {
        $this->info('Creating a new flashcard...');
        // Implementation will be added later
    }

    /**
     * Delete a flashcard.
     */
    protected function deleteFlashcard(): void
    {
        $this->info('Deleting a flashcard...');
        // Implementation will be added later
    }

    /**
     * Edit/practice flashcards.
     */
    protected function editFlashcard(): void
    {
        $this->info('Practicing flashcards...');
        // Implementation will be added later
    }

    /**
     * Show statistics.
     */
    protected function statisticsFlashcards(): void
    {
        $this->info('Showing statistics...');
        // Implementation will be added later
    }

    /**
     * Reset flashcard practice data.
     */
    protected function resetFlashcard(): void
    {
        $this->info('Resetting flashcard data...');
        // Implementation will be added later
    }

    /**
     * Exit the Flashcard interactive command.
     */
    protected function exitCommand(): void
    {
        render('<p class="p-3 bg-red-600 text-white font-bold">See you!</p>');
        $this->shouldKeepRunning = false;
    }
}
