<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\CreateFlashcardAction;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class CreateFlashcardActionTest extends TestCase
{
    private Command $command;

    private ConsoleRendererInterface $renderer;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->command = $this->createMock(Command::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);
    }

    #[Test]
    public function it_executes_create_action(): void
    {
        // Expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Creating a new flashcard...');

        // Create action
        $action = new CreateFlashcardAction($this->command, $this->renderer);

        // Execute
        $action->execute();
    }
}
