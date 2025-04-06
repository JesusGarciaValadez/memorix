<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\ResetFlashcardAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class ResetFlashcardActionTest extends TestCase
{
    private Command $command;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->command = $this->createMock(Command::class);
    }

    #[Test]
    public function it_executes_reset_action(): void
    {
        // Expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Resetting flashcard data...');

        // Create action
        $action = new ResetFlashcardAction($this->command);

        // Execute
        $action->execute();
    }
}
