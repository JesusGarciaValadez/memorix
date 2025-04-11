<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Console\Commands\Actions;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Console\Commands\Actions\ViewLogsAction;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Services\LogServiceInterface;
use Tests\TestCase;

final class ViewLogsActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private bool $shouldKeepRunning = true;

    private FlashcardInteractiveCommand $command;

    private ConsoleRendererInterface $renderer;

    private ViewLogsAction $action;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->command = $this->app->make(FlashcardInteractiveCommand::class);
        $this->command->user = $this->user;
        $logService = $this->app->make(LogServiceInterface::class);
        $this->renderer = $this->app->make(ConsoleRendererInterface::class);
        $this->renderer->enableTestMode(); // Enable test mode to capture output
        $this->action = new ViewLogsAction(
            $this->command,
            $this->shouldKeepRunning,
            $logService,
            $this->renderer
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_user_logs(): void
    {
        // Create some test logs
        Log::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'action' => 'test_action',
            'level' => Log::LEVEL_INFO,
            'details' => 'Test details',
        ]);

        // Execute action and get captured output
        $this->renderer->captureOutput();
        $this->action->execute();
        $output = $this->renderer->getCapturedOutput();

        // Assert output contains expected content
        $this->assertStringContainsString('test_action', $output);
        $this->assertStringContainsString('Test details', $output);
        $this->assertStringContainsString(Log::LEVEL_INFO, $output);
        $this->assertStringNotContainsString('ID', $output);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_message_when_no_logs_exist(): void
    {
        // Execute action and get captured output
        $this->renderer->captureOutput();
        $this->action->execute();
        $output = $this->renderer->getCapturedOutput();

        // Assert output contains expected message
        $this->assertStringContainsString('No activity logs found', $output);
    }

    /**
     * @throws BindingResolutionException
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_errors_gracefully(): void
    {
        // Mock the log service to throw an exception
        $this->mock(LogServiceInterface::class)
            ->shouldReceive('getLatestActivityForUser')
            ->once()
            ->andThrow(new Exception('Test error'));

        // Create a new action with the mocked service
        $action = new ViewLogsAction(
            $this->command,
            $this->shouldKeepRunning,
            $this->app->make(LogServiceInterface::class),
            $this->renderer
        );

        // Execute action and get captured output
        $this->renderer->captureOutput();
        $action->execute();
        $output = $this->renderer->getCapturedOutput();

        // Assert error message is displayed
        $this->assertStringContainsString('An error occurred while fetching logs', $output);
        $this->assertStringContainsString('Test error', $output);
    }
}
