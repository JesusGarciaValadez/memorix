<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\StudySessionService;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StudySessionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MockInterface $studySessionRepository;

    private MockInterface $flashcardRepository;

    private MockInterface $logRepository;

    private MockInterface $statisticRepository;

    private StudySessionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studySessionRepository = Mockery::mock(StudySessionRepositoryInterface::class);
        $this->flashcardRepository = Mockery::mock(FlashcardRepositoryInterface::class);
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        $this->service = new StudySessionService(
            $this->studySessionRepository,
            $this->flashcardRepository,
            $this->logRepository,
            $this->statisticRepository
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
        $userId = 1;
        $session = new StudySession();
        $session->user_id = $userId;
        $session->started_at = now();

        $log = new Log();
        $log->action = 'started_study_session';

        // Expect
        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($userId)
            ->andReturn($session);

        $this->logRepository->shouldReceive('logStudySessionStart')
            ->once()
            ->with($userId, $session)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementStudySessions')
            ->once()
            ->with($userId)
            ->andReturn(true);

        // Act
        $result = $this->service->startSession($userId);

        // Assert
        $this->assertSame($session, $result);
    }

    #[Test]
    public function it_ends_session_returns_false_when_session_not_found(): void
    {
        // Arrange
        $userId = 1;
        $sessionId = 5;

        // Expect
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($sessionId, $userId)
            ->andReturn(null);

        // Act
        $result = $this->service->endSession($userId, $sessionId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_session_returns_false_when_session_already_ended(): void
    {
        // Arrange
        $userId = 1;
        $sessionId = 5;
        $session = new StudySession();
        $session->id = $sessionId;
        $session->user_id = $userId;
        $session->started_at = now()->subHour();
        $session->ended_at = now();

        // Expect
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($sessionId, $userId)
            ->andReturn($session);

        // Act
        $result = $this->service->endSession($userId, $sessionId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_session_ends_session_and_logs_when_active(): void
    {
        // Arrange
        $userId = 1;
        $sessionId = 5;
        $session = new StudySession();
        $session->id = $sessionId;
        $session->user_id = $userId;
        $session->started_at = now()->subHour();
        $session->ended_at = null;

        $log = new Log();
        $log->action = 'ended_study_session';

        // Expect
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($sessionId, $userId)
            ->andReturn($session);

        $this->studySessionRepository->shouldReceive('endSession')
            ->once()
            ->with($session)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logStudySessionEnd')
            ->once()
            ->with($userId, $session)
            ->andReturn($log);

        // Act
        $result = $this->service->endSession($userId, $sessionId);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_gets_flashcards_for_practice_ensures_active_session_exists(): void
    {
        // Arrange
        $userId = 1;
        $session = new StudySession();
        $session->user_id = $userId;
        $session->started_at = now();

        $log = new Log();
        $log->action = 'started_study_session';

        $flashcards = [
            ['id' => 1, 'question' => 'Q1', 'answer' => 'A1'],
            ['id' => 2, 'question' => 'Q2', 'answer' => 'A2'],
        ];

        // Expect
        $this->studySessionRepository->shouldReceive('getActiveSessionForUser')
            ->once()
            ->with($userId)
            ->andReturn(null);

        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($userId)
            ->andReturn($session);

        $this->logRepository->shouldReceive('logStudySessionStart')
            ->once()
            ->with($userId, $session)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementStudySessions')
            ->once()
            ->with($userId)
            ->andReturn(true);

        $this->studySessionRepository->shouldReceive('getFlashcardsForPractice')
            ->once()
            ->with($userId)
            ->andReturn($flashcards);

        // Act
        $result = $this->service->getFlashcardsForPractice($userId);

        // Assert
        $this->assertEquals($flashcards, $result);
    }

    #[Test]
    public function it_records_practice_result_returns_false_when_flashcard_not_found(): void
    {
        // Arrange
        $userId = 1;
        $flashcardId = 5;
        $isCorrect = true;

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcardId, $userId)
            ->andReturn(null);

        // Act
        $result = $this->service->recordPracticeResult($userId, $flashcardId, $isCorrect);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_records_practice_result_records_result_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $userId = 1;
        $flashcardId = 5;
        $isCorrect = true;
        $flashcard = new Flashcard();
        $flashcard->id = $flashcardId;
        $flashcard->user_id = $userId;
        $flashcard->question = 'Test question?';

        $log = new Log();
        $log->action = 'practiced_flashcard';

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcardId, $userId)
            ->andReturn($flashcard);

        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($userId, $flashcardId, $isCorrect)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardPractice')
            ->once()
            ->with($userId, $flashcard, $isCorrect)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementCorrectAnswers')
            ->once()
            ->with($userId)
            ->andReturn(true);

        // Act
        $result = $this->service->recordPracticeResult($userId, $flashcardId, $isCorrect);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_records_practice_result_increments_incorrect_answers_when_wrong(): void
    {
        // Arrange
        $userId = 1;
        $flashcardId = 5;
        $isCorrect = false;
        $flashcard = new Flashcard();
        $flashcard->id = $flashcardId;
        $flashcard->user_id = $userId;
        $flashcard->question = 'Test question?';

        $log = new Log();
        $log->action = 'practiced_flashcard';

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcardId, $userId)
            ->andReturn($flashcard);

        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($userId, $flashcardId, $isCorrect)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardPractice')
            ->once()
            ->with($userId, $flashcard, $isCorrect)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementIncorrectAnswers')
            ->once()
            ->with($userId)
            ->andReturn(true);

        // Act
        $result = $this->service->recordPracticeResult($userId, $flashcardId, $isCorrect);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_resets_practice_progress_performs_reset_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $userId = 1;
        $log = new Log();
        $log->action = 'reset_practice_progress';

        // Expect
        $this->studySessionRepository->shouldReceive('resetPracticeProgress')
            ->once()
            ->with($userId)
            ->andReturn(true);

        $this->statisticRepository->shouldReceive('resetPracticeStats')
            ->once()
            ->with($userId)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logPracticeReset')
            ->once()
            ->with($userId)
            ->andReturn($log);

        // Act
        $result = $this->service->resetPracticeProgress($userId);

        // Assert
        $this->assertTrue($result);
    }
}
