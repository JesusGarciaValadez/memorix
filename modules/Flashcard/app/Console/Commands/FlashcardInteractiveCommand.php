<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use AllowDynamicProperties;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[AllowDynamicProperties]
final class FlashcardInteractiveCommand extends Command implements Isolatable
{
    public ?User $user = null;

    public bool $shouldKeepRunning = true;

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
        {--logs : View user activity logs}
        {--trash-bin : Access the trash bin}
    ';

    protected $description = 'Display a main menu of available Flashcard options';

    public function __construct(
        private readonly FlashcardCommandServiceInterface $commandService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->trap([SIGTERM, SIGABRT, SIGQUIT], function (int $signal): void {
            $this->shouldKeepRunning = false;
        });

        // Handle primary command options first
        if ($this->option('register')) {
            $this->user = $this->commandService->registerUser($this);

            if ($this->user->exists()) {
                $this->alert('User registered successfully!');
            } else {
                $this->alert('The user was not registered successfully!');
            }
            $this->shouldKeepRunning = false;

            return;
        }

        if (
            $this->hasArgument('email') &&
            $this->argument('email') &&
            ! User::where('email', $this->argument('email'))->exists()
        ) {
            $this->error('The email is not registered. Please, register first.');
            $this->user = $this->commandService->registerUser($this);
        }

        if (
            ! $this->user?->exists() &&
            (
                ($this->hasArgument('email') && ! is_null($this->argument('email'))) &&
                ($this->hasArgument('password') && ! is_null($this->argument('password')))
            )
        ) {
            $this->user = $this->validateUserInformation();
        }

        if (! $this->user instanceof User) {
            $this->error('Could not validate or find user. Exiting.');

            return;
        }

        // Check for direct action options
        if ($this->option('list')) {
            $this->commandService->listFlashcards($this->user, $this);

            return;
        }

        if ($this->option('create')) {
            $this->commandService->createFlashcard($this->user, $this);

            return;
        }

        if ($this->option('delete')) {
            $this->commandService->deleteFlashcard($this->user, $this);

            return;
        }

        if ($this->option('practice')) {
            $this->commandService->practiceFlashcards($this->user, $this);

            return;
        }

        if ($this->option('statistics')) {
            $this->commandService->showStatistics($this->user, $this);

            return;
        }

        if ($this->option('reset')) {
            $this->commandService->resetPracticeData($this->user, $this);

            return;
        }

        if ($this->option('logs')) {
            $this->commandService->viewLogs($this->user, $this);

            return;
        }

        if ($this->option('trash-bin')) {
            $this->commandService->accessTrashBin($this->user, $this);

            return;
        }

        $welcomeMessage = sprintf(
            'Hi, %s. Welcome to your flashcards',
            $this->user->name
        );
        $this->info($welcomeMessage);
        // If no specific action option was provided, enter the interactive menu
        while ($this->shouldKeepRunning) {
            $action = $this->displayMenuOption();
            $this->executeAction($action);
        }
    }

    /**
     * Get the isolatable ID for the command.
     */
    public function isolatableId(): string
    {
        return (string) $this->argument('email');
    }

    public function validateUserInformation(): ?User
    {
        // Get email and password
        $email = $this->argument('email') ?? text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            validate: [
                'email' => [
                    'required',
                    'email',
                    'exists:users,email',
                ],
            ],
            transform: fn (string $value): string => mb_trim($value)
        );

        $this->argument('password') ?? password(
            label: 'Enter your password:',
            placeholder: '********',
            validate: fn (string $value): ?string => match (true) {
                null === ($hashedPassword = User::where('email', $email)->value('password')) => 'User not found.',
                ! is_string($hashedPassword) => 'Could not retrieve password hash.',
                ! Hash::check($value, $hashedPassword) => 'Invalid password. Please try again.',
                default => null,
            },
            transform: fn (string $value): string => mb_trim($value)
        );

        return User::where('email', $email)->first();
    }

    /**
     * Display a select menu for flashcard options.
     */
    private function displayMenuOption(): string
    {
        return (string) select(
            label: 'Please, select an option:',
            options: [
                'list' => 'List Flashcards',
                'create' => 'Create Flashcard',
                'delete' => 'Delete Flashcard',
                'practice' => 'Practice Study Mode',
                'statistics' => 'Statistics',
                'reset' => 'Reset the flashcards data',
                'logs' => 'View Activity Logs',
                'trash-bin' => 'Flashcards Trash Bin',
                'exit' => 'Exit',
            ],
            default: 'practice',
            scroll: 9,
            hint: 'Use arrow keys to navigate and press Enter to select an option.',
        );
    }

    /**
     * Execute a flashcard action based on the selected option.
     */
    private function executeAction(string $action): void
    {
        assert($this->user instanceof User);
        match ($action) {
            'list' => $this->commandService->listFlashcards($this->user, $this),
            'create' => $this->commandService->createFlashcard($this->user, $this),
            'delete' => $this->commandService->deleteFlashcard($this->user, $this),
            'practice' => $this->commandService->practiceFlashcards($this->user, $this),
            'statistics' => $this->commandService->showStatistics($this->user, $this),
            'reset' => $this->commandService->resetPracticeData($this->user, $this),
            'logs' => $this->commandService->viewLogs($this->user, $this),
            'trash-bin' => $this->commandService->accessTrashBin($this->user, $this),
            'exit' => $this->exitCommand(),
            default => $this->error("Invalid action: {$action}"),
        };
    }

    /**
     * Exit the command.
     */
    private function exitCommand(): void
    {
        assert($this->user instanceof User);
        $this->commandService->logExit($this->user, $this);
        $this->shouldKeepRunning = false;
    }
}
