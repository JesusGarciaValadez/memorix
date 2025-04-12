<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use AllowDynamicProperties;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[AllowDynamicProperties]
final class FlashcardInteractiveCommand extends Command implements Isolatable
{
    public ?User $user;

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

    protected bool $shouldKeepRunning = true;

    public function __construct(
        private readonly ConsoleRendererInterface $renderer,
        private readonly FlashcardCommandServiceInterface $commandService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->trap([SIGTERM, SIGABRT, SIGQUIT], fn () => $this->shouldKeepRunning = false);

        // Handle primary command options first
        if ($this->option('register')) {
            $this->user = $this->commandService->registerUser();

            return;
        }

        $this->user = $this->validateUserInformation();

        if (is_null($this->user)) {
            $this->renderer->error('User not found. Please register first.');
            $this->user = $this->commandService->registerUser();

            return;
        }

        $this->renderer->success('Hi '.$this->user?->name.', welcome to your flashcards');

        // Check for direct action options
        if ($this->option('list')) {
            $this->commandService->listFlashcards($this->user);

            return;
        }

        if ($this->option('create')) {
            $this->commandService->createFlashcard($this->user);

            return;
        }

        if ($this->option('delete')) {
            $this->commandService->deleteFlashcard($this->user);

            return;
        }

        if ($this->option('practice')) {
            $this->commandService->practiceFlashcards($this->user);

            return;
        }

        if ($this->option('statistics')) {
            $this->commandService->showStatistics($this->user);

            return;
        }

        if ($this->option('reset')) {
            $this->commandService->resetPracticeData($this->user);

            return;
        }

        if ($this->option('logs')) {
            $this->commandService->viewLogs($this->user);

            return;
        }

        if ($this->option('trash-bin')) {
            $this->commandService->accessTrashBin($this->user);

            return;
        }

        // If no specific action option was provided, enter the interactive menu
        while ($this->shouldKeepRunning) {
            $action = $this->selectMenuOption();
            $this->executeAction($action);
        }
    }

    /**
     * Get the isolatable ID for the command.
     */
    public function isolatableId(): string
    {
        return $this->argument('email');
    }

    public function validateUserInformation(): ?User
    {
        // Get email and password
        $email = $this->argument('email') ?? text(
            label: 'Enter your user email:',
            placeholder: 'john@doe.com',
            required: true,
            validate: ['email' => 'required|email|exists:users,email'],
            transform: fn (string $value) => mb_trim($value)
        );

        $this->argument('password') ?? password(
            label: 'Enter your password:',
            placeholder: '********',
            required: true,
            validate: fn (string $value) => match (true) {
                ! \Illuminate\Support\Facades\Hash::check(
                    $value,
                    User::where('email', $email)->value('password')
                ) => 'Invalid password. Please try again.',
                default => null,
            },
            transform: fn (string $value) => mb_trim($value)
        );

        return User::where('email', $email)->first();
    }

    /**
     * Display a select menu for flashcard options.
     */
    protected function selectMenuOption(): string
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
    protected function executeAction(string $action): void
    {
        match ($action) {
            'list' => $this->commandService->listFlashcards($this->user),
            'create' => $this->commandService->createFlashcard($this->user),
            'delete' => $this->commandService->deleteFlashcard($this->user),
            'practice' => $this->commandService->practiceFlashcards($this->user),
            'statistics' => $this->commandService->showStatistics($this->user),
            'reset' => $this->commandService->resetPracticeData($this->user),
            'logs' => $this->commandService->viewLogs($this->user),
            'trash-bin' => $this->commandService->accessTrashBin($this->user),
            'exit' => $this->exitCommand(),
            default => $this->renderer->error("Invalid action: {$action}"),
        };
    }

    /**
     * Exit the command.
     */
    private function exitCommand(): void
    {
        $this->commandService->logExit($this->user);
        $this->shouldKeepRunning = false;
    }
}
