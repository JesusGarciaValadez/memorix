<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\Eloquent\PracticeResultRepository;
use Tests\TestCase;

final class PracticeResultRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PracticeResultRepository $repository;

    private User $user;

    private Flashcard $flashcard;

    private StudySession $studySession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new PracticeResultRepository();

        // Create test data
        $this->user = User::factory()->create();
        $this->flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $this->studySession = StudySession::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_it_creates_a_practice_result(): void
    {
        // Act
        $result = $this->repository->create(
            $this->user->id,
            $this->flashcard->id,
            $this->studySession->id,
            true
        );

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('practice_results', [
            'user_id' => $this->user->id,
            'flashcard_id' => $this->flashcard->id,
            'study_session_id' => $this->studySession->id,
            'is_correct' => true,
        ]);
    }

    public function test_it_gets_practice_results_for_user(): void
    {
        // Arrange
        PracticeResult::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        PracticeResult::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $results = $this->repository->getForUser($this->user->id);

        // Assert
        $this->assertCount(3, $results);
        $this->assertSame($this->user->id, $results->first()->user_id);
    }

    public function test_it_gets_practice_results_for_flashcard(): void
    {
        // Arrange
        PracticeResult::factory()->count(2)->create([
            'flashcard_id' => $this->flashcard->id,
        ]);

        PracticeResult::factory()->create([
            'flashcard_id' => Flashcard::factory()->create(['user_id' => $this->user->id])->id,
        ]);

        // Act
        $results = $this->repository->getForFlashcard($this->flashcard->id);

        // Assert
        $this->assertCount(2, $results);
        $this->assertSame($this->flashcard->id, $results->first()->flashcard_id);
    }

    public function test_it_gets_practice_results_for_study_session(): void
    {
        // Arrange
        PracticeResult::factory()->count(2)->create([
            'study_session_id' => $this->studySession->id,
        ]);

        PracticeResult::factory()->create([
            'study_session_id' => StudySession::factory()->create(['user_id' => $this->user->id])->id,
        ]);

        // Act
        $results = $this->repository->getForStudySession($this->studySession->id);

        // Assert
        $this->assertCount(2, $results);
        $this->assertSame($this->studySession->id, $results->first()->study_session_id);
    }

    public function test_it_deletes_practice_results_for_user(): void
    {
        // Arrange
        PracticeResult::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $otherUser = User::factory()->create();
        PracticeResult::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $result = $this->repository->deleteForUser($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('practice_results', [
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('practice_results', [
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_it_checks_if_flashcard_has_been_practiced_recently(): void
    {
        // Arrange
        PracticeResult::factory()->create([
            'flashcard_id' => $this->flashcard->id,
            'created_at' => now()->subDays(2),
        ]);

        // Act
        $result = $this->repository->hasBeenPracticedRecently($this->flashcard->id);

        // Assert
        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_flashcard_has_not_been_practiced_recently(): void
    {
        // Arrange
        PracticeResult::factory()->create([
            'flashcard_id' => $this->flashcard->id,
            'created_at' => now()->subDays(10),
        ]);

        // Act
        $result = $this->repository->hasBeenPracticedRecently($this->flashcard->id, 7);

        // Assert
        $this->assertFalse($result);
    }

    public function test_it_gets_recently_incorrect_flashcards(): void
    {
        // Arrange
        // Create a flashcard with incorrect results
        $flashcard1 = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Question 1',
            'answer' => 'Answer 1',
        ]);

        PracticeResult::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcard1->id,
            'is_correct' => false,
            'created_at' => now()->subDays(1),
        ]);

        // Create another flashcard with correct results
        $flashcard2 = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Question 2',
            'answer' => 'Answer 2',
        ]);

        PracticeResult::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcard2->id,
            'is_correct' => true,
            'created_at' => now()->subDays(1),
        ]);

        // Act
        $results = $this->repository->getRecentlyIncorrectFlashcards($this->user->id);

        // Assert
        $this->assertCount(1, $results);
        $this->assertSame($flashcard1->id, $results[0]['id']);
        $this->assertSame('Question 1', $results[0]['question']);
        $this->assertSame('Answer 1', $results[0]['answer']);
    }
}
