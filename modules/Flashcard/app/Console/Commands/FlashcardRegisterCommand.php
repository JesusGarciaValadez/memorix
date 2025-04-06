<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Termwind\render;

final class FlashcardRegisterCommand extends Command implements Isolatable, PromptsForMissingInput
{
    protected $signature = 'flashcard:register
        {name : The name of the user}
        {email : The email of the user}
        {password : The password of the user}
   ';

    protected $description = 'Register a new user';

    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
        parent::__construct();
    }

    public function handle(): void
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

            render('<p class="p-3 bg-green-600 text-white font-bold">User '.$name.' registered successfully with email '.$email.'.</p>');
        } catch (QueryException $exception) {
            render('<p class="p-3 bg-red-600 text-white font-bold">An error occurred: '.$exception->getMessage().'</p>');

            return;
        }

        $this->call('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
        ]);
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
