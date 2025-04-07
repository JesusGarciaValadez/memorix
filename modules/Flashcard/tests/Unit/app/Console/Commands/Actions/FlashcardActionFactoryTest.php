<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Flashcard\app\Console\Commands\Actions\CreateFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\DeleteFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\ExitCommandAction;
use Modules\Flashcard\app\Console\Commands\Actions\FlashcardActionFactory;
use Modules\Flashcard\app\Console\Commands\Actions\ListFlashcardsAction;
use Modules\Flashcard\app\Console\Commands\Actions\PracticeFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\ResetFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\StatisticsFlashcardAction;
use Modules\Flashcard\app\Services\FlashcardService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

final class FlashcardActionFactoryTest extends TestCase
{
    use RefreshDatabase;

    private Command $command;

    private bool $shouldKeepRunning;

    private FlashcardService $flashcardService;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->command = $this->createMock(Command::class);
        $this->shouldKeepRunning = true;

        // Create a real FlashcardService instance for testing
        $this->flashcardService = app(FlashcardService::class);
        // Bind this instance to the app container so the factory can resolve it
        $this->app->instance(FlashcardService::class, $this->flashcardService);
    }

    #[Test]
    public function it_creates_list_action(): void
    {
        $action = FlashcardActionFactory::create('list', $this->command);
        $this->assertInstanceOf(ListFlashcardsAction::class, $action);
    }

    #[Test]
    public function it_creates_create_action(): void
    {
        $action = FlashcardActionFactory::create('create', $this->command);
        $this->assertInstanceOf(CreateFlashcardAction::class, $action);
    }

    #[Test]
    public function it_creates_delete_action(): void
    {
        $action = FlashcardActionFactory::create('delete', $this->command);
        $this->assertInstanceOf(DeleteFlashcardAction::class, $action);
    }

    #[Test]
    public function it_creates_practice_action(): void
    {
        $action = FlashcardActionFactory::create('practice', $this->command);
        $this->assertInstanceOf(PracticeFlashcardAction::class, $action);
    }

    #[Test]
    public function it_creates_statistics_action(): void
    {
        $action = FlashcardActionFactory::create('statistics', $this->command);
        $this->assertInstanceOf(StatisticsFlashcardAction::class, $action);
    }

    #[Test]
    public function it_creates_reset_action(): void
    {
        $action = FlashcardActionFactory::create('reset', $this->command);
        $this->assertInstanceOf(ResetFlashcardAction::class, $action);
    }

    #[Test]
    public function it_creates_exit_action(): void
    {
        $action = FlashcardActionFactory::create('exit', $this->command, $this->shouldKeepRunning);
        $this->assertInstanceOf(ExitCommandAction::class, $action);
    }

    #[Test]
    public function it_throws_exception_for_invalid_action(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid action: invalid');
        FlashcardActionFactory::create('invalid', $this->command);
    }
}
