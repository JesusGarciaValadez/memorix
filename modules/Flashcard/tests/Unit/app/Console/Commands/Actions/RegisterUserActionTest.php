<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\Actions\RegisterUserAction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class RegisterUserActionTest extends TestCase
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
        putenv('TERMWIND_SILENT=true');
    }

    protected function tearDown(): void
    {
        putenv('TERMWIND_SILENT');
        parent::tearDown();
    }

    #[Test]
    public function it_executes_the_register_user__action(): void
    {
        // Create action with the reference to command
        $action = new RegisterUserAction($this->command, $this->shouldKeepRunning);

        // Execute
        $action->execute();

        // Assert that shouldKeepRunning is now false
        $this->assertTrue($this->shouldKeepRunning);
    }
}
