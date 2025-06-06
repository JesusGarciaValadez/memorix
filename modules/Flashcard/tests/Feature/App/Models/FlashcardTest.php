<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_only_be_accessed_by_its_owner(): void
    {
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        $otherUser = User::factory()->create();

        // Owner can access
        $found = Flashcard::findForUser($flashcard->id, $this->user->id);
        $this->assertNotNull($found);

        // Other user cannot access
        $notFound = Flashcard::findForUser($flashcard->id, $otherUser->id);
        $this->assertNull($notFound);
    }

    #[Test]
    public function it_can_be_soft_deleted_and_restored(): void
    {
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        // Delete the flashcard
        $flashcard->delete();
        $deletedFlashcard = Flashcard::findForUser($flashcard->id, $this->user->id);
        $this->assertNull($deletedFlashcard);

        // Restore the flashcard
        $flashcard->restore();
        $restored = Flashcard::findForUser($flashcard->id, $this->user->id);
        $this->assertNotNull($restored);
        $this->assertEquals($flashcard->id, $restored->id);
    }

    #[Test]
    public function it_can_track_practice_results(): void
    {
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        // Create a study session
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);

        // Add practice results
        $flashcard->practiceResults()->create([
            'user_id' => $this->user->id,
            'study_session_id' => $studySession->id,
            'is_correct' => true,
        ]);

        $this->assertTrue($flashcard->isCorrectlyAnswered());
        $this->assertFalse($flashcard->isIncorrectlyAnswered());
    }

    #[Test]
    public function it_can_handle_bulk_operations(): void
    {
        // Create multiple flashcards
        $flashcards = Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);

        // Verify they can be retrieved
        $retrieved = Flashcard::getAllForUser($this->user->id);
        $this->assertCount(3, $retrieved);

        // Delete all
        $flashcards->each->delete();
        $this->assertCount(0, Flashcard::getAllForUser($this->user->id));

        // Restore all
        Flashcard::restoreAllForUser($this->user->id);
        $this->assertCount(3, Flashcard::getAllForUser($this->user->id));

        // Force delete all
        Flashcard::forceDeleteAllForUser($this->user->id);
        $this->assertCount(0, Flashcard::getAllForUser($this->user->id));
    }

    #[Test]
    public function it_can_handle_edge_cases(): void
    {
        // Test with empty question
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => '',
            'answer' => 'An answer',
        ]);
        $this->assertNotNull($flashcard);

        // Test with very long question and answer
        $longText = str_repeat('a', 500);
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => $longText,
            'answer' => $longText,
        ]);
        $this->assertNotNull($flashcard);

        // Test with special characters
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => 'What is @#$%^&*()?',
            'answer' => 'Special characters!',
        ]);
        $this->assertNotNull($flashcard);
    }

    #[Test]
    public function it_can_be_soft_deleted_and_restored_with_actual_deleted_at(): void
    {
        $flashcard = Flashcard::factory()->for($this->user)->create();
        $flashcard->delete();

        // Reload the model to get the actual deleted_at timestamp from DB
        $reloadedFlashcard = Flashcard::withTrashed()->find($flashcard->id);
        $this->assertNotNull($reloadedFlashcard?->deleted_at);
    }
}
