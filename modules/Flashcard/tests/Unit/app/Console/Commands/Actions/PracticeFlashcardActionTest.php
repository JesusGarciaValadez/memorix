<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\PracticeFlashcardAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class PracticeFlashcardActionTest extends TestCase
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
    public function it_executes_practice_action(): void
    {
        // Expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Practicing flashcards...');

        // Create action
        $action = new PracticeFlashcardAction($this->command);

        // Execute
        $action->execute();
    }
}
