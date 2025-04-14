<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\app\Console\Commands;

use App\Models\User;
use Mockery;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardRegisterCommandTest extends TestCase
{
    private FlashcardCommandServiceInterface $commandService;

    private ConsoleRendererInterface $renderer;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandService = Mockery::mock(FlashcardCommandServiceInterface::class);
        $this->app->instance(FlashcardCommandServiceInterface::class, $this->commandService);

        $this->renderer = $this->app->make(ConsoleRendererInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    #[Test]
    public function it_registers_a_new_user(): void
    {
        // Create a user to return
        $user = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Setup mock
        $this->commandService->shouldReceive('registerUser')
            ->once()
            ->andReturn($user);

        // Execute command
        $this->artisan('flashcard:register')
            ->expectsConfirmation('Do you want to use the flashcard application now?', 'no')
            ->assertSuccessful();
    }
}
