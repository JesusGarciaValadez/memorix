<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Prompts\Prompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardInteractiveCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable interactive prompts
        Prompt::fake([
            // Default mock values for various prompt types
            'Enter your user email:' => 'test@example.com',
            'Enter your password:' => 'password',
            'Please, select an option:' => 'exit',
        ]);
    }

    #[Test]
    public function it_handles_normal_flow_without_email_and_password_arguments(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'name' => 'Test User',
        ]);

        // Use PendingCommand for testing
        $this->withoutMockingConsoleOutput()
            ->artisan('flashcard:interactive')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_handles_flow_with_email_argument(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'name' => 'Test User',
        ]);

        // Use PendingCommand for testing
        $this->withoutMockingConsoleOutput()
            ->artisan('flashcard:interactive', ['email' => 'test@example.com'])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_handles_flow_with_email_and_password_arguments(): void
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'name' => 'Test User',
        ]);

        // Use PendingCommand for testing
        $this->withoutMockingConsoleOutput()
            ->artisan('flashcard:interactive', [
                'email' => 'test@example.com',
                'password' => 'password',
            ])
            ->assertExitCode(0);
    }

    #[Test]
    public function it_handles_flow_with_register_option(): void
    {
        // Testing the register option directly
        $this->withoutMockingConsoleOutput()
            ->artisan('flashcard:interactive', ['--register' => true])
            ->assertExitCode(0);
    }
}
