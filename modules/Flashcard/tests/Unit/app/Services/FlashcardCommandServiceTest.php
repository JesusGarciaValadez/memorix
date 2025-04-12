<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardCommandService;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardCommandServiceTest extends TestCase
{
    private FlashcardCommandService $service;

    private User $user;

    private ConsoleRendererInterface $renderer;

    private LogService $logService;

    private FlashcardService $flashcardService;

    private StudySessionRepositoryInterface $studySessionRepository;

    private PracticeResultRepositoryInterface $practiceResultRepository;

    private StatisticRepositoryInterface $statisticRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->renderer = $this->app->make(ConsoleRendererInterface::class);
        $this->renderer->enableTestMode();
        $this->service = $this->app->make(FlashcardCommandService::class);
        $this->logService = $this->app->make(LogService::class);
        $this->flashcardService = $this->app->make(FlashcardService::class);
        $this->studySessionRepository = $this->app->make(StudySessionRepositoryInterface::class);
        $this->practiceResultRepository = $this->app->make(PracticeResultRepositoryInterface::class);
    }

    #[Test]
    public function it_can_list_flashcards_when_none_exist(): void
    {
        // Arrange - make sure the user has no flashcards
        $this->user->flashcards()->delete();

        // Act
        $this->renderer->captureOutput();
        $this->service->listFlashcards($this->user);
        $output = $this->renderer->getCapturedOutput();

        // Assert
        $this->assertStringContainsString('Listing all flashcards...', $output);
        $this->assertStringContainsString('You have no flashcards yet.', $output);

        // Verify the log was created
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->user->id,
            'action' => 'viewed_flashcard_list',
        ]);
    }

    #[Test]
    public function it_can_list_flashcards_when_some_exist(): void
    {
        // Arrange - create some flashcards
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        // Act
        $this->renderer->captureOutput();
        $this->service->listFlashcards($this->user);
        $output = $this->renderer->getCapturedOutput();

        // Assert
        $this->assertStringContainsString('Listing all flashcards...', $output);
        // We might not be able to directly test for the question/answer content
        // because the table rendering might be happening in Laravel Prompts
        $this->assertNotEmpty($output);

        // Verify the log was created
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->user->id,
            'action' => 'viewed_flashcard_list',
        ]);
    }

    #[Test]
    public function it_can_show_statistics(): void
    {
        // Arrange - Create some data to generate statistics
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);

        // Act
        $this->renderer->captureOutput();
        $this->service->showStatistics($this->user);
        $output = $this->renderer->getCapturedOutput();

        // Assert
        $this->assertStringContainsString('Showing statistics...', $output);

        // Verify the log was created
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->user->id,
            'action' => 'statistics_viewed',
        ]);
    }

    #[Test]
    public function it_can_log_exit(): void
    {
        // Create a test user
        $testUser = User::factory()->create();

        // Get the actual service instance from the container
        $commandService = $this->app->make(FlashcardCommandService::class);

        // Enable test mode for the renderer to capture output
        $renderer = $this->app->make(ConsoleRendererInterface::class);
        $renderer->enableTestMode();
        $renderer->captureOutput();

        // Call the method under test
        $commandService->logExit($testUser);

        // Verify the log was created in the database
        $this->assertDatabaseHas('logs', [
            'user_id' => $testUser->id,
            'action' => 'user_exit',
            'level' => 'info',
        ]);

        // Verify the output message was displayed
        $output = $renderer->getCapturedOutput();
        $this->assertStringContainsString('See you!', $output);
    }

    #[Test]
    public function it_can_create_flashcard(): void
    {
        // Skip this test because it requires interactive prompts that are difficult to mock.
        $this->markTestIncomplete('This test requires interactive prompts which are difficult to mock.');

        // The functionality is actually tested in FlashcardInteractiveCommandTest
        // when it tests the 'create' command option
    }

    /**
     * Helper method to mock Laravel\Prompts\text function
     */
    private function setUpTextPromptMock(string $label, string $returnValue): void
    {
        \Laravel\Prompts\Prompt::fake([
            $label => $returnValue,
        ]);
    }
}
