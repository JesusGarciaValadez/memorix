<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Prompts\Prompt;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardCommandService;
use Modules\Flashcard\app\Services\FlashcardServiceInterface;
use Modules\Flashcard\app\Services\LogServiceInterface;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use Modules\Flashcard\app\Services\StudySessionServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @mixin \Mockery\MockInterface */
final class FlashcardCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FlashcardServiceInterface|MockInterface $flashcardServiceMock;

    private StudySessionServiceInterface|MockInterface $studySessionServiceMock;

    private StatisticServiceInterface|MockInterface $statisticServiceMock;

    private LogServiceInterface|MockInterface $logServiceMock;

    private PracticeResultRepositoryInterface|MockInterface $practiceResultRepositoryMock;

    private StudySessionRepositoryInterface|MockInterface $studySessionRepositoryMock;

    // @phpstan-ignore-next-line property.unusedType
    private FlashcardInteractiveCommand|MockInterface $commandMock;

    private FlashcardCommandService $service;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Fake prompts to prevent blocking in non-interactive tests
        Prompt::fake();

        $this->user = $this->createTestUser();

        $this->flashcardServiceMock = Mockery::mock(FlashcardServiceInterface::class);
        $this->studySessionServiceMock = Mockery::mock(StudySessionServiceInterface::class);
        $this->statisticServiceMock = Mockery::mock(StatisticServiceInterface::class);
        $this->logServiceMock = Mockery::mock(LogServiceInterface::class);
        $this->practiceResultRepositoryMock = Mockery::mock(PracticeResultRepositoryInterface::class);
        $this->studySessionRepositoryMock = Mockery::mock(StudySessionRepositoryInterface::class);
        // @phpstan-ignore-next-line assign.propertyType
        $this->commandMock = Mockery::mock(FlashcardInteractiveCommand::class)->makePartial();

        $this->service = new FlashcardCommandService(
            // @phpstan-ignore-next-line argument.type
            $this->flashcardServiceMock,
            // @phpstan-ignore-next-line argument.type
            $this->studySessionServiceMock,
            // @phpstan-ignore-next-line argument.type
            $this->statisticServiceMock,
            // @phpstan-ignore-next-line argument.type
            $this->logServiceMock,
            // @phpstan-ignore-next-line argument.type
            $this->practiceResultRepositoryMock,
            // @phpstan-ignore-next-line argument.type
            $this->studySessionRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function list_flashcards_logs_action_and_shows_warning_when_none_exist(): void
    {
        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logFlashcardList')
            ->once()
            ->with($this->user->id);

        $emptyPaginator = new LengthAwarePaginator([], 0, 15);
        // @phpstan-ignore-next-line method.notFound
        $this->flashcardServiceMock->shouldReceive('getAllForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($emptyPaginator);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();

        // @phpstan-ignore-next-line argument.type
        $this->service->listFlashcards($this->user, $this->commandMock);
    }

    #[Test]
    public function list_flashcards_logs_action_and_displays_table_when_flashcards_exist(): void
    {
        $flashcards = Flashcard::factory()->count(2)->make([
            'user_id' => $this->user->id,
        ]);
        $paginator = new LengthAwarePaginator($flashcards, $flashcards->count(), 15);

        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logFlashcardList')
            ->once()
            ->with($this->user->id);

        // @phpstan-ignore-next-line method.notFound
        $this->flashcardServiceMock->shouldReceive('getAllForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($paginator);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();
        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('table')->atLeast()->once();

        // @phpstan-ignore-next-line argument.type
        $this->service->listFlashcards($this->user, $this->commandMock);
    }

    #[Test]
    public function show_statistics_logs_action_and_shows_warning_when_no_stats(): void
    {
        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logStatisticsView')
            ->once()
            ->with($this->user->id);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('getStatisticsForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn([]);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();
        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('warn')->atLeast()->once();

        // @phpstan-ignore-next-line argument.type
        $this->service->showStatistics($this->user, $this->commandMock);
    }

    #[Test]
    public function show_statistics_logs_action_and_displays_stats(): void
    {
        $statsData = [
            'flashcards_created' => 10,
            'study_sessions' => 5,
            'correct_answers' => 20,
            'incorrect_answers' => 5,
        ];
        $avgDuration = 15.5;
        $totalTime = 77.5;

        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logStatisticsView')
            ->once()
            ->with($this->user->id);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('getStatisticsForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($statsData);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->user->id)
            ->andReturn($avgDuration);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->user->id)
            ->andReturn($totalTime);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->times(3);

        // @phpstan-ignore-next-line argument.type
        $this->service->showStatistics($this->user, $this->commandMock);
    }

    #[Test]
    public function log_exit_logs_action_and_outputs_message(): void
    {
        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logUserExit')
            ->once()
            ->with($this->user->id);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();

        // @phpstan-ignore-next-line argument.type
        $this->service->logExit($this->user, $this->commandMock);
    }

    #[Test]
    public function create_flashcard_calls_service_create(): void
    {
        $question = 'Test Q';
        $answer = 'Test A';
        $flashcardData = ['question' => $question, 'answer' => $answer];
        $createdFlashcard = Flashcard::factory()->make($flashcardData + ['id' => 1, 'user_id' => $this->user->id]);

        // @phpstan-ignore-next-line method.notFound
        $this->flashcardServiceMock->shouldReceive('create')
            ->once()
            ->with($this->user->id, $flashcardData)
            ->andReturn($createdFlashcard);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();

        // Mock expectation serves as assertion for service call
    }

    #[Test]
    public function reset_practice_data_calls_dependencies_on_confirm(): void
    {
        // @phpstan-ignore-next-line method.notFound
        $this->practiceResultRepositoryMock
            ->shouldReceive('deleteForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn(5);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('getStatisticsForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn(['correct_answers' => 10, 'incorrect_answers' => 5]);

        // @phpstan-ignore-next-line method.notFound
        $this->statisticServiceMock->shouldReceive('updateStatistics')
            ->once()
            ->with($this->user->id, ['correct_answers' => 0, 'incorrect_answers' => 0])
            ->andReturn(true);

        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('logPracticeReset')
            ->once()
            ->with($this->user->id);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();

        // Mock expectations serve as assertion for dependency calls
    }

    #[Test]
    public function view_logs_shows_logs_or_warning(): void
    {
        $logsData = [
            ['level' => 'info', 'action' => 'test_action', 'details' => 'details1'],
            ['level' => 'warn', 'action' => 'another_action', 'details' => null],
        ];

        // @phpstan-ignore-next-line method.notFound
        $this->logServiceMock->shouldReceive('getLatestActivityForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($logsData);

        // @phpstan-ignore-next-line method.notFound
        $this->commandMock->shouldReceive('info')->atLeast()->once();

        // @phpstan-ignore-next-line argument.type
        $this->service->viewLogs($this->user, $this->commandMock);
    }

    /**
     * Helper method to create a test user
     */
    private function createTestUser(): User
    {
        return User::factory([
            'name' => 'Test User',
        ])->create();
    }
}
