<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Modules\Flashcard\app\Console\Commands\Actions\CreateFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\DeleteFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\ExitCommandAction;
use Modules\Flashcard\app\Console\Commands\Actions\FlashcardActionFactory;
use Modules\Flashcard\app\Console\Commands\Actions\ListFlashcardsAction;
use Modules\Flashcard\app\Console\Commands\Actions\PracticeFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\ResetFlashcardAction;
use Modules\Flashcard\app\Console\Commands\Actions\StatisticsFlashcardAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class FlashcardActionFactoryTest extends TestCase
{
    private Command $command;

    private bool $shouldKeepRunning;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->command = $this->createMock(Command::class);
        $this->shouldKeepRunning = true;
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
