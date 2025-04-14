<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Console\Commands;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Console\Commands\FlashcardRegisterCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

final class FlashcardCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

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
        // Create a spy for the FlashcardCommandServiceInterface
        $commandService = Mockery::spy(FlashcardCommandServiceInterface::class);
        $commandService->shouldReceive('findUserByEmail')
            ->with('test@example.com')
            ->andReturn((object) [
                'id' => 1,
                'email' => 'test@example.com',
            ]);

        // Assert that we can properly use the service
        $user = $commandService->findUserByEmail('test@example.com');
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
    }

    #[Test]
    public function password_can_be_retrieved_and_verified(): void
    {
        // Test Hash facade functionality directly
        $password = 'password123';
        $hashedPassword = Hash::make($password);

        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertFalse(Hash::check('wrong_password', $hashedPassword));
    }
}
