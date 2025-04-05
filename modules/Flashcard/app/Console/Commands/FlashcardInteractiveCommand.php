<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Termwind\render;

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

    public function handle(): void
    {
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
        $user = User::where('email', $email);

        $password = $this->argument('password') ?? password(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: fn (string $value) => match (true) {
                ! Hash::check(
                    $value,
                    $user->pluck('password')->first()
                ) => 'Invalid password. Please try again.',
                default => null,
            },
            transform: fn (string $value) => mb_trim($value)
        );
        $user = $user->first();

        render('<p class="p-3 bg-green-600 text-white font-bold">Hi '.$user->name.', welcome to your flashcards</p>');
        select(
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
}
