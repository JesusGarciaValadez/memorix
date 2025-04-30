<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\LogServiceInterface;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use Modules\Flashcard\app\Services\StudySessionService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface&StudySessionRepositoryInterface $studySessionRepository;

    private MockInterface&FlashcardRepositoryInterface $flashcardRepository;

    private MockInterface&LogServiceInterface $logService;

    private MockInterface&StatisticServiceInterface $statisticService;

    private StudySessionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&StudySessionRepositoryInterface $studySessionRepository */
        $studySessionRepository = Mockery::mock(StudySessionRepositoryInterface::class);
        $this->studySessionRepository = $studySessionRepository;

        /** @var MockInterface&FlashcardRepositoryInterface $flashcardRepository */
        $flashcardRepository = Mockery::mock(FlashcardRepositoryInterface::class);
        $this->flashcardRepository = $flashcardRepository;

        /** @var MockInterface&LogServiceInterface $logService */
        $logService = Mockery::mock(LogServiceInterface::class);
        $this->logService = $logService;

        /** @var MockInterface&StatisticServiceInterface $statisticService */
        $statisticService = Mockery::mock(StatisticServiceInterface::class);
        $this->statisticService = $statisticService;

        $this->service = new StudySessionService(
            $this->studySessionRepository,
            $this->flashcardRepository,
            $this->logService,
            $this->statisticService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_starts_session_creates_session_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $log = Log::factory()->create([
            'user_id' => $user->id,
            'action' => 'started_study_session',
            'details' => [
                'session_id' => $session->id,
            ],
        ]);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($user->id)
            ->andReturn($session);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logStudySessionStart')
            ->once()
            ->with($user->id, $session)
            ->andReturn($log);

        // @phpstan-ignore-next-line
        $this->statisticService->shouldReceive('incrementStudySessions')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // Act
        $result = $this->service->startSession($user->id);

        // Assert
        $this->assertSame($session, $result);
    }

    #[Test]
    public function it_ends_session_returns_false_when_session_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($session->id, $user->id)
            ->andReturn(null);

        // Act
        $result = $this->service->endSession($user->id, $session->id);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_session_returns_false_when_session_already_ended(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($session->id, $user->id)
            ->andReturn($session);

        // Act
        $result = $this->service->endSession($user->id, $session->id);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_session_ends_session_and_logs_when_active(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subHour(),
            'ended_at' => null,
        ]);

        $log = new Log();
        $log->action = 'ended_study_session';

        // Expect
        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($session->id, $user->id)
            ->andReturn($session);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('endSession')
            ->once()
            ->with($session)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logStudySessionEnd')
            ->once()
            ->with($user->id, $session)
            ->andReturn($log);

        // Act
        $result = $this->service->endSession($user->id, $session->id);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_gets_flashcards_for_practice_ensures_active_session_exists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $log = Log::factory()->create([
            'user_id' => $user->id,
            'action' => 'started_study_session',
        ]);

        $flashcards = [
            ['id' => 1, 'question' => 'Q1', 'answer' => 'A1'],
            ['id' => 2, 'question' => 'Q2', 'answer' => 'A2'],
        ];

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('getActiveSessionForUser')
            ->once()
            ->with($user->id)
            ->andReturn(null);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($user->id)
            ->andReturn($session);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logStudySessionStart')
            ->once()
            ->with($user->id, $session)
            ->andReturn($log);

        // @phpstan-ignore-next-line
        $this->statisticService->shouldReceive('incrementStudySessions')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('getFlashcardsForPractice')
            ->once()
            ->with($user->id)
            ->andReturn($flashcards);

        // Act
        $result = $this->service->getFlashcardsForPractice($user->id);

        // Assert
        $this->assertEquals($flashcards, $result);
    }

    #[Test]
    public function it_records_practice_result_returns_false_when_flashcard_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question?',
            'answer' => 'Test answer?',
            'created_at' => now(),
        ]);
        StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        $practiceResult = PracticeResult::factory()->create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);

        // @phpstan-ignore-next-line
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcard->id, $user->id)
            ->andReturn(null);

        // Act
        $result = $this->service->recordPracticeResult($user->id, $flashcard->id, $practiceResult->is_correct);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_records_practice_result_records_result_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question?',
            'answer' => 'Test answer?',
            'created_at' => now(),
        ]);
        StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        $practiceResult = PracticeResult::factory()->create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);

        $log = Log::factory()->create([
            'action' => 'practiced_flashcard',
        ]);

        // @phpstan-ignore-next-line
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcard->id, $user->id)
            ->andReturn($flashcard);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($user->id, $flashcard->id, $practiceResult->is_correct)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logFlashcardPractice')
            ->once()
            ->with($user->id, $flashcard, $practiceResult->is_correct)
            ->andReturn($log);

        // @phpstan-ignore-next-line
        $this->statisticService->shouldReceive('incrementCorrectAnswers')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // Act
        $result = $this->service->recordPracticeResult($user->id, $flashcard->id, $practiceResult->is_correct);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_records_practice_result_increments_incorrect_answers_when_wrong(): void
    {
        // Arrange
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question?',
            'answer' => 'Test answer?',
            'created_at' => now(),
        ]);
        StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        $practiceResult = PracticeResult::factory()->create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);

        $log = Log::factory()->create([
            'action' => 'practiced_flashcard',
        ]);

        // @phpstan-ignore-next-line
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcard->id, $user->id)
            ->andReturn($flashcard);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($user->id, $flashcard->id, $practiceResult->is_correct)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logFlashcardPractice')
            ->once()
            ->with($user->id, $flashcard, $practiceResult->is_correct)
            ->andReturn($log);

        // @phpstan-ignore-next-line
        $this->statisticService->shouldReceive('incrementIncorrectAnswers')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // Act
        $result = $this->service->recordPracticeResult($user->id, $flashcard->id, $practiceResult->is_correct);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_resets_practice_progress_performs_reset_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question?',
            'answer' => 'Test answer?',
            'created_at' => now(),
        ]);
        StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);
        PracticeResult::factory()->create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);
        $log = Log::factory()->create([
            'action' => 'reset_practice_progress',
        ]);

        // @phpstan-ignore-next-line
        $this->studySessionRepository->shouldReceive('resetPracticeProgress')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->statisticService->shouldReceive('resetPracticeStatistics')
            ->once()
            ->with($user->id)
            ->andReturn(true);

        // @phpstan-ignore-next-line
        $this->logService->shouldReceive('logPracticeReset')
            ->once()
            ->with($user->id)
            ->andReturn($log);

        // Act
        $result = $this->service->resetPracticeProgress($user->id);

        // Assert
        $this->assertTrue($result);
    }
}
