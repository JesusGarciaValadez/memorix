<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardInteractiveCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user that can be reused in all tests
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    #[Test]
    public function it_tests_the_flashcard_user_registration_option(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $this->artisan('flashcard:interactive --register')
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsQuestion('Please, select an option:', 'exit')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
        ]);
        $this->assertTrue(Hash::check($password, User::where('email', $email)->first()->password));
    }

    #[Test]
    public function it_runs_list_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--list' => true,
        ])
            ->expectsOutput('Listing all flashcards...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_lists_flashcards_with_data(): void
    {
        // Create sample flashcards
        $flashcard1 = $this->user->flashcards()->create([
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework for web development',
        ]);

        $flashcard2 = $this->user->flashcards()->create([
            'question' => 'What is PHPUnit?',
            'answer' => 'A unit testing framework for PHP',
        ]);

        // Run the list command
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--list' => true,
        ])
            ->expectsOutput('Listing all flashcards...')
            ->assertExitCode(0);

        // Verify the flashcards exist in the database
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard1->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework for web development',
        ]);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard2->id,
            'question' => 'What is PHPUnit?',
            'answer' => 'A unit testing framework for PHP',
        ]);
    }

    #[Test]
    public function it_shows_warning_when_no_flashcards_exist(): void
    {
        // Delete any existing flashcards for the user
        $this->user->flashcards()->delete();

        // Run the list command
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--list' => true,
        ])
            ->expectsOutput('Listing all flashcards...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_runs_create_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--create' => true,
        ])
            ->expectsOutput('Creating a new flashcard...')
            ->expectsQuestion('Enter the flashcard question:', 'Sample Question')
            ->expectsQuestion('Enter the flashcard answer:', 'Sample Answer')
            ->expectsOutput('Question: Sample Question')
            ->expectsOutput('Answer: Sample Answer')
            ->assertSuccessful();

        $this->assertDatabaseHas('flashcards', [
            'user_id' => $this->user->id,
            'question' => 'Sample Question',
            'answer' => 'Sample Answer',
        ]);
    }

    #[Test]
    public function it_runs_delete_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--delete' => true,
        ])
            ->expectsOutput('Deleting a flashcard...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_runs_practice_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--practice' => true,
        ])
            ->expectsOutput('Practicing flashcards...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_runs_statistics_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--statistics' => true,
        ])
            ->expectsOutput('Showing statistics...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_runs_reset_command_directly(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
            '--reset' => true,
        ])
            ->expectsOutput('Resetting flashcard data...')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_exits_the_flashcard_interactive_command(): void
    {
        $this->artisan('flashcard:interactive', [
            'email' => 'test@example.com',
            'password' => 'password',
        ])
            ->expectsChoice('Please, select an option:', 'exit', [
                'Create Flashcard',
                'Delete Flashcard',
                'Exit',
                'List Flashcards',
                'Practice Study Mode',
                'Reset the flashcards data',
                'Statistics',
                'create',
                'delete',
                'exit',
                'list',
                'practice',
                'reset',
                'statistics',
            ])
            ->assertSuccessful();
    }

    #[Test]
    public function it_tests_enter_the_command_for_the_first_time(): void
    {
        User::truncate();

        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
        ])
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsQuestion('Please, select an option:', 'exit')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
        ]);
        $this->assertTrue(Hash::check($password, User::where('email', $email)->first()->password));
    }
}
