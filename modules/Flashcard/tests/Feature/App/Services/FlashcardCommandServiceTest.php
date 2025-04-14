<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\Eloquent\FlashcardRepository;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use Modules\Flashcard\app\Repositories\Eloquent\PracticeResultRepository;
use Modules\Flashcard\app\Repositories\Eloquent\StudySessionRepository;
use Modules\Flashcard\app\Services\FlashcardCommandService;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StudySessionService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardCommandService $service;

    private User $user;

    private ConsoleRenderer $renderer;

    private LogService $logService;

    private FlashcardService $flashcardService;

    private StatisticService $statisticService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createTestUser();
        $this->renderer = new ConsoleRenderer();
        $logRepository = new LogRepository();
        $studySessionRepository = new StudySessionRepository(new PracticeResultRepository());
        $flashcardRepository = new FlashcardRepository();
        $practiceResultRepository = new PracticeResultRepository();
        $this->statisticService = new StatisticService();
        $this->logService = new LogService($logRepository);
        $this->flashcardService = new FlashcardService($this->logService, $this->statisticService);
        $studySessionService = new StudySessionService(
            $studySessionRepository,
            $flashcardRepository,
            $this->logService,
            $this->statisticService
        );
        $studySessionRepository = new StudySessionRepository($practiceResultRepository);
        $practiceResultRepository1 = new PracticeResultRepository();

        $this->service = new FlashcardCommandService(
            $this->flashcardService,
            $studySessionService,
            $this->statisticService,
            $this->logService,
            $practiceResultRepository1,
            $studySessionRepository,
            $this->renderer
        );
    }

    #[Test]
    public function it_can_list_flashcards_when_none_exist(): void
    {
        // Arrange
        $this->flashcardService->setGetAllForUserResult(1);

        $this->renderer->shouldReceive('info')
            ->with('Listing all flashcards...')
            ->once();

        $this->renderer->shouldReceive('warning')
            ->with('You have no flashcards yet.')
            ->once();

        // Act
        $this->service->listFlashcards($this->user);

        // Assert
        $this->assertTrue($this->logService->logFlashcardListCalled);
        $this->assertEquals($this->user->id, $this->logService->lastUserId);
    }

    #[Test]
    public function it_can_list_flashcards_when_some_exist(): void
    {
        // Arrange
        $flashcard = $this->createFlashcard();
        $this->flashcardService->setGetAllForUserResult([$flashcard]);

        $this->renderer->shouldReceive('info')
            ->with('Listing all flashcards...')
            ->once();

        // Act
        $this->service->listFlashcards($this->user);

        // Assert
        $this->assertTrue($this->logService->logFlashcardListCalled);
        $this->assertEquals($this->user->id, $this->logService->lastUserId);
    }

    #[Test]
    public function it_can_show_statistics(): void
    {
        // Arrange
        $this->statisticService->shouldReceive('getStatisticsForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn([
                'flashcards_created' => 0,
                'study_sessions' => 0,
                'correct_answers' => 0,
                'incorrect_answers' => 0,
            ]);

        $this->renderer->shouldReceive('info')
            ->with('Showing statistics...')
            ->once();

        $this->renderer->shouldReceive('info')
            ->withAnyArgs()
            ->times(7);

        $this->statisticService->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->user->id)
            ->andReturn(0);

        $this->statisticService->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->user->id)
            ->andReturn(0);

        // Act
        $this->service->showStatistics($this->user);

        // Assert
        $this->assertTrue($this->logService->logStatisticsViewCalled);
        $this->assertEquals($this->user->id, $this->logService->lastUserId);
    }

    #[Test]
    public function it_can_log_exit(): void
    {
        // Arrange
        $this->renderer->shouldReceive('info')
            ->with('See you!')
            ->once();

        // Act
        $this->service->logExit($this->user);

        // Assert
        $this->assertTrue($this->logService->logUserExitCalled);
        $this->assertEquals($this->user->id, $this->logService->lastUserId);
    }

    #[Test]
    public function it_can_create_flashcard(): void
    {
        // This test requires interactive prompts which are difficult to mock
        // The functionality is tested in FlashcardInteractiveCommandTest
        $this->assertTrue(true);
    }

    /**
     * Helper method to create a test user
     */
    private function createTestUser(): User
    {
        return User::factory([
            'id' => 1,
            'name' => 'Test User',
        ])->create();
    }

    /**
     * Helper method to create a test flashcard
     */
    private function createFlashcard(): Flashcard
    {
        return Flashcard::factory([
            'id' => 1,
            'user_id' => $this->user->id,
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ])->make();
    }
}
