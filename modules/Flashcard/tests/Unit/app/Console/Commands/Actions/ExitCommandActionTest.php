<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\ExitCommandAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class ExitCommandActionTest extends TestCase
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
    public function it_executes_exit_action(): void
    {
        // Create action with the reference to the shouldKeepRunning variable
        $action = new ExitCommandAction($this->command, $this->shouldKeepRunning);

        // Execute
        $action->execute();

        // Assert that shouldKeepRunning is now false
        $this->assertFalse($this->shouldKeepRunning);
    }
}
