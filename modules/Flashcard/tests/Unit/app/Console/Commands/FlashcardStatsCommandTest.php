<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Console\Commands;

use App\Models\User;
use Mockery;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardStatsCommandTest extends TestCase
{
    private FlashcardCommandServiceInterface $commandService;

    private ConsoleRendererInterface $renderer;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
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
    public function it_shows_statistics(): void
    {
        // Set up mock
        $this->commandService->shouldReceive('showStatistics')
            ->once()
            ->with(Mockery::on(fn ($user) => $user->id === $this->user->id))
            ->andReturn(true);

        $this->artisan('flashcard:stats', [
            'userId' => $this->user->id,
        ])
            ->assertSuccessful();
    }
}
