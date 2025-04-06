<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardRegisterCommandTest extends TestCase
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
        $this->artisan('flashcard:register')
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
    public function it_tests_the_user_registration_user_name_validation(): void
    {
        $name = '';
        $email = 'john@wick.com';
        $this->artisan('flashcard:register')
            ->expectsQuestion('Enter your user name:', $name)
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
        ]);
    }

    #[Test]
    public function it_tests_the_user_registration_user_email_validation(): void
    {
        $name = 'John_Wick';
        $email = 'john';
        $this->artisan('flashcard:register')
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
        ]);
    }

    #[Test]
    public function it_tests_the_user_registration_user_password_validation(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'Password';
        $this->artisan('flashcard:register')
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'name' => str_replace('_', ' ', $name),
            'email' => $email,
        ]);
    }
}
