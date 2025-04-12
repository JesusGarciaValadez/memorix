<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands;

use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Console\Commands\FlashcardRegisterCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;

final class FlashcardCommandsTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_provides_command_service_to_register_command(): void
    {
        $command = $this->app->make(FlashcardRegisterCommand::class);

        $reflection = new ReflectionClass($command);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals(FlashcardCommandServiceInterface::class, $parameters[0]->getType()->getName());
        $this->assertEquals(ConsoleRendererInterface::class, $parameters[1]->getType()->getName());
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    #[Test]
    public function it_provides_necessary_dependencies_to_interactive_command(): void
    {
        $command = $this->app->make(FlashcardInteractiveCommand::class);

        $reflection = new ReflectionClass($command);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals(ConsoleRendererInterface::class, $parameters[0]->getType()->getName());
        $this->assertEquals(FlashcardCommandServiceInterface::class, $parameters[1]->getType()->getName());
    }

    #[Test]
    public function users_can_be_found_by_email(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Test User model's ability to find by email
        $result = User::where('email', 'test@example.com')->first();
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('test@example.com', $result->email);
    }

    #[Test]
    public function password_can_be_retrieved_and_verified(): void
    {
        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Test password retrieval and verification
        $password = User::where('email', 'test@example.com')->value('password');
        $this->assertTrue(Hash::check('password123', $password));
    }
}
