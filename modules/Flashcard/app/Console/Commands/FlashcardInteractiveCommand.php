<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\form;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class FlashcardInteractiveCommand extends Command implements Isolatable, PromptsForMissingInput
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

    public function handle(): void
    {
        if ($this->option('register')) {
            $this->info('Register a new user');
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
                    User::where('email', $email)->pluck('users.password')->first()
                ) => 'Invalid password. Please try again.',
                default => null,
            },
            transform: fn (string $value) => mb_trim($value)
        );

        $this->info('Flashcard Interactive Command');
        select(
            label: 'Select an option:',
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

        // Handle the command logic here
        // For example, you can call other methods based on the options provided
        if ($this->option('list')) {
            // Call the method to create a new flashcard
            $this->listFlashcards();
        } elseif ($this->option('create')) {
            // Call the method to list all flashcards
            $this->createFlashcard();
        } elseif ($this->option('delete')) {
            // Call the method to delete a flashcard
            $this->deleteFlashcard();
        } elseif ($this->option('practice')) {
            // Call the method to practices the flashcards
            $this->editFlashcard();
        } elseif ($this->option('statistics')) {
            // Call the method to show the statistics
            $this->statisticsFlashcards();
        } elseif ($this->option('reset')) {
            // Call the method to practices the flashcards
            $this->resetFlashcard();
        } elseif ($this->option('exit')) {
            // Call the method to delete a flashcard
            $this->exitFlashcards();
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
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'email' => fn () => text(
                label: 'Enter your user email:',
                placeholder: 'john@doe.com',
                validate: ['email' => 'required|email|exists:users,email'],
                transform: fn (string $value) => mb_trim($value)
            ),
            'password' => fn () => password(
                label: 'Enter your password:',
                placeholder: '********',
                validate: fn (string $value) => match (true) {
                    ! Hash::check(
                        $value,
                        User::where('email', $this->argument('email'))->pluck('users.password')->first()
                    ) => 'Invalid password. Please try again.',
                    default => null,
                },
                transform: fn (string $value) => mb_trim($value)
            ),
        ];
    }
}
