<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardRegisterCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private MockInterface $userRepositoryMock;

    private User $testUser;

    private OutputStyle $outputStyleMock;

    protected function setUp(): void
    {
        parent::setUp();
        ConsoleRenderer::enableTestMode();
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepositoryMock);

        $this->outputStyleMock = Mockery::mock(OutputStyle::class);
        $this->app->instance(OutputStyle::class, $this->outputStyleMock);

        // Create a real User instance for testing
        $this->testUser = new User([
            'name' => 'John Wick',
            'email' => 'john@wick.com',
            'password' => Hash::make('Password123!'),
        ]);
    }

    #[Test]
    public function it_tests_the_flashcard_user_registration_option(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($args) {
                return $args['name'] === 'Test User' &&
                    $args['email'] === 'test@example.com' &&
                    Hash::check('Password123!', $args['password']);
            }))
            ->andReturn($user);

        $this->artisan('flashcard:register', [
            'name' => 'Test_User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            '--skip-interactive' => true,
        ])->assertSuccessful();

        $this->assertEquals(
            'User Test User registered successfully with email test@example.com.',
            mb_trim(ConsoleRenderer::getTestOutput())
        );
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

    #[Test]
    public function it_handles_duplicate_email_error_gracefully(): void
    {
        $exception = new QueryException(
            'sqlite',
            'insert into "users" ("email", "name", "password") values (?, ?, ?)',
            ['test@example.com', 'Test User', 'hashed_password'],
            new Exception('SQLSTATE[23000]')
        );

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($args) {
                return $args['name'] === 'Test User' &&
                    $args['email'] === 'test@example.com' &&
                    Hash::check('Password123!', $args['password']);
            }))
            ->andThrow($exception);

        $this->artisan('flashcard:register', [
            'name' => 'Test_User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            '--skip-interactive' => true,
        ])->assertFailed();

        $this->assertEquals(
            'A user with this email already exists. Please try logging in instead.',
            mb_trim(ConsoleRenderer::getTestOutput())
        );
    }
}
