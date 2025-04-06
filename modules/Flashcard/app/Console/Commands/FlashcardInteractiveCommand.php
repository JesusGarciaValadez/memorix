<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use AllowDynamicProperties;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Console\Commands\Actions\FlashcardActionFactory;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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

        ConsoleRenderer::success('Hi '.$user->name.', welcome to your flashcards');

        // Check for direct action options
        if ($this->option('list')) {
            $this->executeAction('list');

            return;
        }

        if ($this->option('create')) {
            $this->executeAction('create');

            return;
        }

        if ($this->option('delete')) {
            $this->executeAction('delete');

            return;
        }

        if ($this->option('practice')) {
            $this->executeAction('practice');

            return;
        }

        if ($this->option('statistics')) {
            $this->executeAction('statistics');

            return;
        }

        if ($this->option('reset')) {
            $this->executeAction('reset');

            return;
        }

        // If no specific action option was provided, enter the interactive menu
        while ($this->shouldKeepRunning) {
            $selectedOption = $this->selectMenuOption();
            $this->executeAction($selectedOption);
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
                'exit' => 'Exit',
            ],
            default: 'practice',
            scroll: 8,
            hint: 'Use arrow keys to navigate and press Enter to select an option.',
        );
    }

    /**
     * Execute a flashcard action.
     */
    protected function executeAction(string $action): void
    {
        $flashcardAction = FlashcardActionFactory::create($action, $this, $this->shouldKeepRunning);
        $flashcardAction->execute();
    }
}
