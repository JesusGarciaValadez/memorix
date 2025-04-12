<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\App\Repositories\Eloquent;

use App\Models\User;
//
use Modules\Flashcard\App\Models\Flashcard;
use Modules\Flashcard\App\Models\StudySession;
use Modules\Flashcard\App\Repositories\Eloquent\PracticeResultRepository;
use Modules\Flashcard\App\Repositories\Eloquent\StudySessionRepository;
use Modules\Flashcard\Tests\TestCase;

final class StudySessionRepositoryTest extends TestCase
{
    // Remove RefreshDatabase since it's already in the parent TestCase
    //

    private User $user;

    private StudySessionRepository $repository;

    private StudySession $studySession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->repository = new StudySessionRepository(
            $this->app->make(PracticeResultRepository::class)
        );
        $this->studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);
    }

    public function test_it_can_start_session(): void
    {
        $session = $this->repository->startSession($this->user->id);
        $this->assertNotNull($session);
        $this->assertEquals($this->user->id, $session->user_id);
        $this->assertNotNull($session->started_at);
        $this->assertNull($session->ended_at);
    }

    public function test_it_can_end_session(): void
    {
        $result = $this->repository->endSession($this->studySession);
        $this->assertTrue($result);
        $this->studySession->refresh();
        $this->assertNotNull($this->studySession->ended_at);
    }

    public function test_it_can_find_session_for_user(): void
    {
        $session = $this->repository->findForUser($this->studySession->id, $this->user->id);
        $this->assertNotNull($session);
        $this->assertEquals($this->user->id, $session->user_id);
    }

    public function test_it_can_get_active_session(): void
    {
        $session = $this->repository->getActiveSessionForUser($this->user->id);
        $this->assertNotNull($session);
        $this->assertEquals($this->user->id, $session->user_id);
    }

    public function test_it_can_get_flashcards_for_practice(): void
    {
        $flashcards = $this->repository->getFlashcardsForPractice($this->user->id);
        $this->assertIsArray($flashcards);
    }

    public function test_it_can_record_practice_result(): void
    {
        // Arrange
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $repository = new StudySessionRepository(
            new PracticeResultRepository()
        );

        // Act - ensure we're passing a boolean (true) for the isCorrect parameter
        $result = $repository->recordPracticeResult(
            $user->id,
            $flashcard->id,
            true
        );

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('practice_results', [
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);
    }

    public function test_it_can_reset_practice_progress(): void
    {
        $result = $this->repository->resetPracticeProgress($this->user->id);
        $this->assertTrue($result);
    }

    public function test_it_can_delete_all_for_user(): void
    {
        $result = $this->repository->deleteAllForUser($this->user->id);
        $this->assertTrue($result);
    }

    public function test_it_can_get_latest_result_for_flashcard(): void
    {
        $result = $this->repository->getLatestResultForFlashcard(1);
        $this->assertNull($result); // Since we haven't created any practice results
    }
}
