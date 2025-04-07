<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Console\Commands\Actions\ExitCommandAction;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TestCommand extends Command
{
    public function getUser()
    {
        return (object) ['id' => 1];
    }
}

final class ExitCommandActionTest extends TestCase
{
    use RefreshDatabase;

    private Command $command;

    private bool $shouldKeepRunning;

    private LogRepository $logRepository;

    private ExitCommandAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new TestCommand();
        $this->shouldKeepRunning = true;
        $this->logRepository = new LogRepository();

        // Create a test user
        User::factory()->create(['id' => 1]);

        $this->action = new ExitCommandAction(
            $this->command,
            $this->shouldKeepRunning,
            $this->logRepository
        );
    }

    protected function tearDown(): void
    {
        putenv('TERMWIND_SILENT');
        parent::tearDown();
    }

    #[Test]
    public function it_executes_exit_action(): void
    {
        $this->action->execute();
        $this->assertFalse($this->shouldKeepRunning);
    }
}
