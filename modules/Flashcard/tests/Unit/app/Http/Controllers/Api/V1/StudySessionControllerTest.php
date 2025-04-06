<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\StudySessionController;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\StudySessionService;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class StudySessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $studySessionRepository;

    private MockInterface $flashcardRepository;

    private MockInterface $logRepository;

    private MockInterface $statisticRepository;

    private StudySessionController $controller;

    private MockInterface $request;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studySessionRepository = Mockery::mock(StudySessionRepositoryInterface::class);
        $this->flashcardRepository = Mockery::mock(FlashcardRepositoryInterface::class);
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        // Create a real StudySessionService with the mocked repositories
        $studySessionService = new StudySessionService(
            $this->studySessionRepository,
            $this->flashcardRepository,
            $this->logRepository,
            $this->statisticRepository
        );

        $this->controller = new StudySessionController($studySessionService);

        // Create a real user instance
        $this->user = User::factory()->create();

        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('user')->andReturn($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_starts_a_new_study_session(): void
    {
        // Arrange
        $studySession = new StudySession();
        $studySession->id = 1;
        $studySession->user_id = $this->user->id;
        $studySession->started_at = now();

        $log = new Log();
        $log->action = 'started_study_session';

        // Expect
        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($this->user->id)
            ->andReturn($studySession);

        $this->logRepository->shouldReceive('logStudySessionStart')
            ->once()
            ->with($this->user->id, $studySession)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementStudySessions')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        // Act
        $response = $this->controller->start($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $jsonContent = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($jsonContent);
        $this->assertObjectHasProperty('id', $jsonContent);
        $this->assertObjectHasProperty('user_id', $jsonContent);
        $this->assertObjectHasProperty('started_at', $jsonContent);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_ends_an_active_study_session_successfully(): void
    {
        // Arrange
        $studySessionId = 1;
        $studySession = new StudySession();
        $studySession->id = $studySessionId;
        $studySession->user_id = $this->user->id;
        $studySession->started_at = now()->subHour();
        $studySession->ended_at = null;

        $log = new Log();
        $log->action = 'ended_study_session';

        // Expect
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($studySessionId, $this->user->id)
            ->andReturn($studySession);

        $this->studySessionRepository->shouldReceive('endSession')
            ->once()
            ->with($studySession)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logStudySessionEnd')
            ->once()
            ->with($this->user->id, $studySession)
            ->andReturn($log);

        // Act
        $response = $this->controller->end($this->request, $studySessionId);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['message' => 'Study session ended successfully'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_not_found_when_ending_non_existent_session(): void
    {
        // Arrange
        $studySessionId = 999;

        // Expect
        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($studySessionId, $this->user->id)
            ->andReturn(null);

        // Act
        $response = $this->controller->end($this->request, $studySessionId);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(['message' => 'Study session not found or already ended'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_gets_flashcards_for_practice(): void
    {
        // Arrange
        $studySession = new StudySession();
        $studySession->id = 1;
        $studySession->user_id = $this->user->id;
        $studySession->started_at = now();

        $log = new Log();
        $log->action = 'started_study_session';

        $flashcards = [
            ['id' => 1, 'question' => 'Test question 1', 'answer' => 'Test answer 1'],
            ['id' => 2, 'question' => 'Test question 2', 'answer' => 'Test answer 2'],
        ];

        // Expect
        $this->studySessionRepository->shouldReceive('getActiveSessionForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($studySession);

        $this->studySessionRepository->shouldReceive('getFlashcardsForPractice')
            ->once()
            ->with($this->user->id)
            ->andReturn($flashcards);

        // Act
        $response = $this->controller->getFlashcardsForPractice($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($flashcards, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_records_practice_result_successfully(): void
    {
        // Arrange
        $flashcardId = 1;
        $isCorrect = true;

        $flashcard = new Flashcard();
        $flashcard->id = $flashcardId;
        $flashcard->user_id = $this->user->id;
        $flashcard->question = 'Test question';

        $log = new Log();
        $log->action = 'practiced_flashcard';

        $this->request->shouldReceive('input')
            ->once()
            ->with('is_correct', false)
            ->andReturn(true);

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcardId, $this->user->id)
            ->andReturn($flashcard);

        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($this->user->id, $flashcardId, $isCorrect)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardPractice')
            ->once()
            ->with($this->user->id, $flashcard, $isCorrect)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementCorrectAnswers')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        // Act
        $response = $this->controller->recordPracticeResult($this->request, $flashcardId);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['message' => 'Practice result recorded successfully'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_not_found_when_recording_for_non_existent_flashcard(): void
    {
        // Arrange
        $flashcardId = 999;

        $this->request->shouldReceive('input')
            ->once()
            ->with('is_correct', false)
            ->andReturn(true);

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcardId, $this->user->id)
            ->andReturn(null);

        // Act
        $response = $this->controller->recordPracticeResult($this->request, $flashcardId);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard not found'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_resets_practice_progress(): void
    {
        // Arrange
        $log = new Log();
        $log->action = 'reset_practice_progress';

        // Expect
        $this->studySessionRepository->shouldReceive('resetPracticeProgress')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        $this->statisticRepository->shouldReceive('resetPracticeStats')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logPracticeReset')
            ->once()
            ->with($this->user->id)
            ->andReturn($log);

        // Act
        $response = $this->controller->resetPractice($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['message' => 'Practice progress reset successfully'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
