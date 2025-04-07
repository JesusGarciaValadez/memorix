<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class FlashcardRegisterCommand extends Command implements Isolatable, PromptsForMissingInput
{
    protected $signature = 'flashcard:register
        {name : The name of the user}
        {email : The email of the user}
        {password : The password of the user}
        {--skip-interactive : Skip the interactive part}
   ';

    protected $description = 'Register a new user';

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = str_replace('_', ' ', $this->argument('name'));
        $email = $this->argument('email');
        $password = $this->argument('password');

        try {
            $this->userRepository->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            ConsoleRenderer::success('User '.$name.' registered successfully with email '.$email.'.');
        } catch (QueryException $exception) {
            // Check for unique constraint violation
            if (str_contains($exception->getMessage(), 'SQLSTATE[23000]')) {
                $this->error('A user with this email already exists. Please try logging in instead.');

                return Command::FAILURE;
            }

            $this->error('An error occurred while registering the user. Please try again.');

            return Command::FAILURE;
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            $this->error('A user with this email already exists. Please try logging in instead.');

            return Command::FAILURE;
        }

        if (! $this->option('skip-interactive')) {
            $this->call('flashcard:interactive', [
                'email' => $email,
                'password' => $password,
            ]);
        }

        return Command::SUCCESS;
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
            'name' => fn () => text(
                label: 'Enter your user name:',
                placeholder: 'John_Doe',
                required: true,
                validate: ['string' => 'required|string|min:2|max:255'],
                transform: fn (string $value) => mb_trim($value)
            ),
            'email' => fn () => text(
                label: 'Enter your user email:',
                placeholder: 'john@doe.com',
                required: true,
                validate: ['email' => 'required|email|unique:users,email'],
                transform: fn (string $value) => mb_trim($value)
            ),
            'password' => fn () => password(
                label: 'Enter your password:',
                placeholder: '********',
                required: true,
                validate: fn (string $value) => match (true) {
                    ! preg_match('/[A-Z]/', $value) => 'Password must contain at least one uppercase letter.',
                    ! preg_match('/[a-z]/', $value) => 'Password must contain at least one lowercase letter.',
                    ! preg_match('/\d/', $value) => 'Password must contain at least one number.',
                    ! preg_match('/[]!#$%^&*()_+={}|;:\'",.<>?]/', $value) => 'Password must contain at least one special character.',
                    ! preg_match('/\S/', $value) => 'Password must not contain spaces.',
                    Str::length($value) <= 8 => 'Password must be at least 8 characters long.',
                    default => null,
                },
                transform: fn (string $value) => mb_trim($value)
            ),
        ];
    }
}
