<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\Eloquent\StudySessionRepository;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StudySessionRepository $repository;

    private MockInterface $practiceResultRepository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practiceResultRepository = Mockery::mock(PracticeResultRepositoryInterface::class);
        $this->repository = new StudySessionRepository($this->practiceResultRepository);
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_starts_a_new_study_session(): void
    {
        // Act
        $result = $this->repository->startSession($this->user->id);

        // Assert
        $this->assertInstanceOf(StudySession::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertNotNull($result->started_at);
        $this->assertNull($result->ended_at);
    }

    #[Test]
    public function it_ends_a_study_session(): void
    {
        // Arrange
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'ended_at' => null,
        ]);

        // Act
        $result = $this->repository->endSession($studySession);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('study_sessions', [
            'id' => $studySession->id,
            'user_id' => $this->user->id,
        ]);
        $studySession->refresh();
        $this->assertNotNull($studySession->ended_at);
    }

    #[Test]
    public function it_finds_a_study_session_for_user(): void
    {
        // Arrange
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
        ]);

        StudySession::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $result = $this->repository->findForUser($studySession->id, $this->user->id);

        // Assert
        $this->assertInstanceOf(StudySession::class, $result);
        $this->assertEquals($studySession->id, $result->id);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    #[Test]
    public function it_returns_null_when_study_session_not_found_for_user(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $studySession = StudySession::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $result = $this->repository->findForUser($studySession->id, $this->user->id);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_active_session_for_user(): void
    {
        // Arrange
        $oldSession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->subDays(2),
            'ended_at' => now()->subDays(1),
        ]);

        $activeSession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->subHours(1),
            'ended_at' => null,
        ]);

        // Act
        $result = $this->repository->getActiveSessionForUser($this->user->id);

        // Assert
        $this->assertInstanceOf(StudySession::class, $result);
        $this->assertEquals($activeSession->id, $result->id);
        $this->assertNull($result->ended_at);
    }

    #[Test]
    public function it_returns_null_when_no_active_session_for_user(): void
    {
        // Arrange
        StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => now()->subDays(2),
            'ended_at' => now()->subDays(1),
        ]);

        // Act
        $result = $this->repository->getActiveSessionForUser($this->user->id);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_flashcards_for_practice(): void
    {
        // Arrange
        $incorrectFlashcards = [
            [
                'id' => 1,
                'question' => 'Question 1',
                'answer' => 'Answer 1',
            ],
        ];

        // Mock the practice result repository to return incorrect flashcards
        $this->practiceResultRepository->shouldReceive('getRecentlyIncorrectFlashcards')
            ->once()
            ->with($this->user->id)
            ->andReturn($incorrectFlashcards);

        Flashcard::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->getFlashcardsForPractice($this->user->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('question', $result[0]);
        $this->assertArrayHasKey('answer', $result[0]);
        $this->assertEquals(1, $result[0]['id']);
    }

    #[Test]
    public function it_records_practice_result(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'ended_at' => null,
        ]);

        // Mock the practice result repository
        $this->practiceResultRepository->shouldReceive('create')
            ->once()
            ->with($this->user->id, $flashcard->id, $studySession->id, true)
            ->andReturn(true);

        // Act
        $result = $this->repository->recordPracticeResult($this->user->id, $flashcard->id, true);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_creates_a_new_session_when_recording_result_without_active_session(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // No active session should exist

        // Mock the practice result repository
        $this->practiceResultRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($userId, $flashcardId, $sessionId, $isCorrect) {
                // Check that we have a valid session ID
                $this->assertNotNull($sessionId);
                // Verify the session exists
                $this->assertDatabaseHas('study_sessions', ['id' => $sessionId]);

                return true;
            });

        // Act
        $result = $this->repository->recordPracticeResult($this->user->id, $flashcard->id, true);

        // Assert
        $this->assertTrue($result);

        // Should have created a new session
        $session = StudySession::where('user_id', $this->user->id)
            ->whereNull('ended_at')
            ->first();

        $this->assertNotNull($session);
    }

    #[Test]
    public function it_resets_practice_progress(): void
    {
        // Arrange
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'ended_at' => null,
        ]);

        // Mock the practice result repository
        $this->practiceResultRepository->shouldReceive('deleteForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        // Act
        $result = $this->repository->resetPracticeProgress($this->user->id);

        // Assert
        $this->assertTrue($result);

        // Session should be ended
        $studySession->refresh();
        $this->assertNotNull($studySession->ended_at);
    }

    #[Test]
    public function it_resets_practice_progress_without_active_session(): void
    {
        // Arrange - no active session

        // Mock the practice result repository
        $this->practiceResultRepository->shouldReceive('deleteForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        // Act
        $result = $this->repository->resetPracticeProgress($this->user->id);

        // Assert
        $this->assertTrue($result);
    }
}
