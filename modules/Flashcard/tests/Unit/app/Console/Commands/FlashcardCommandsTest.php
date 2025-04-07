<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Console\Commands\FlashcardRegisterCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\Eloquent\UserRepository;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

final class FlashcardCommandsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_binds_user_repository_in_container(): void
    {
        // Test that the UserRepositoryInterface is bound to UserRepository
        $this->assertInstanceOf(
            UserRepository::class,
            $this->app->make(UserRepositoryInterface::class)
        );
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_injects_user_repository_into_register_command(): void
    {
        // Test that the command has a dependency on UserRepositoryInterface
        $command = $this->app->make(FlashcardRegisterCommand::class);

        // Use reflection to check if UserRepositoryInterface is injected
        $reflection = new ReflectionClass($command);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals(UserRepositoryInterface::class, $parameters[0]->getType()->getName());
        $this->assertEquals(ConsoleRendererInterface::class, $parameters[1]->getType()->getName());
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_injects_user_repository_into_interactive_command(): void
    {
        // Test that the command has a dependency on UserRepositoryInterface
        $command = $this->app->make(FlashcardInteractiveCommand::class);

        // Use reflection to check if UserRepositoryInterface is injected
        $reflection = new ReflectionClass($command);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals(UserRepositoryInterface::class, $parameters[0]->getType()->getName());
        $this->assertEquals(ConsoleRendererInterface::class, $parameters[1]->getType()->getName());
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function user_repository_can_find_user_by_email(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Get the actual repository from the container
        $repository = $this->app->make(UserRepositoryInterface::class);

        // Test the findByEmail method
        $result = $repository->findByEmail('test@example.com');
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('test@example.com', $result->email);
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function user_repository_can_get_password_by_email(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Get the actual repository from the container
        $repository = $this->app->make(UserRepositoryInterface::class);

        // Test the getPasswordByEmail method
        $password = $repository->getPasswordByEmail('test@example.com');
        $this->assertTrue(Hash::check('password123', $password));
    }
}
