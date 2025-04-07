<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\DeleteFlashcardAction;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteFlashcardActionTest extends TestCase
{
    private Command $command;

    private ConsoleRendererInterface $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('TERMWIND_SILENT=true');
        $this->command = $this->createMock(Command::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);
    }

    protected function tearDown(): void
    {
        putenv('TERMWIND_SILENT');
        parent::tearDown();
    }

    #[Test]
    public function it_executes_delete_action(): void
    {
        // Expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Deleting a flashcard...');

        // Create action
        $action = new DeleteFlashcardAction($this->command, $this->renderer);

        // Execute
        $action->execute();
    }
}
