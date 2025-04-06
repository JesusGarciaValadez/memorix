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

        if ($this->option('register')) {
            $this->call('flashcard:register');

            return;
        }

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
        $option = select(
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

        while ($this->shouldKeepRunning) {
            // Handle the command logic here
            // For example, you can call other methods based on the options provided
            if ($option('list')) {
                // Call the method to list all flashcards
                $this->listFlashcards();
            } elseif ($option('create')) {
                // Call the method to create a new flashcard
                $this->createFlashcard();
            } elseif ($option('delete')) {
                // Call the method to delete a flashcard
                $this->deleteFlashcard();
            } elseif ($option('practice')) {
                // Call the method to practices the flashcards
                $this->editFlashcard();
            } elseif ($option('statistics')) {
                // Call the method to show the statistics
                $this->statisticsFlashcards();
            } elseif ($option('reset')) {
                // Call the method to reset all the statistics
                $this->resetFlashcard();
            } elseif ($this->option('exit')) {
                render('<p class="p-3 bg-red-600 text-white font-bold">See you!</p>');
                $this->shouldKeepRunning = false;
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
}
