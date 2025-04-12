<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\Eloquent\FlashcardRepository;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardRepositoryTest extends TestCase
{
    private FlashcardRepository $repository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FlashcardRepository();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_gets_all_flashcards_for_user(): void
    {
        // Arrange
        Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        Flashcard::factory()->count(2)->create([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $result = $this->repository->getAllForUser($this->user->id);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
        $this->assertEquals(3, $result->total());
    }

    #[Test]
    public function it_gets_all_deleted_flashcards_for_user(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $flashcards->take(3)->each(function (Flashcard $flashcard) {
            $flashcard->delete();
        });

        Flashcard::factory()->count(2)->create([
            'user_id' => User::factory()->create()->id,
        ])->each(function (Flashcard $flashcard) {
            $flashcard->delete();
        });

        // Act
        $result = $this->repository->getAllDeletedForUser($this->user->id);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
        $this->assertEquals(3, $result->total());
    }

    #[Test]
    public function it_finds_flashcard_for_user(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Flashcard::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $result = $this->repository->findForUser($flashcard->id, $this->user->id);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($flashcard->id, $result->id);
    }

    #[Test]
    public function it_returns_null_when_flashcard_not_found_for_user(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $result = $this->repository->findForUser($flashcard->id, $this->user->id);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_finds_soft_deleted_flashcard_when_with_trashed_is_true(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $flashcard->delete();

        // Act
        $result = $this->repository->findForUser($flashcard->id, $this->user->id, true);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($flashcard->id, $result->id);
    }

    #[Test]
    public function it_creates_a_new_flashcard(): void
    {
        // Arrange
        $data = [
            'user_id' => $this->user->id,
            'question' => 'Test question',
            'answer' => 'Test answer',
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('Test question', $result->question);
        $this->assertEquals('Test answer', $result->answer);
        $this->assertDatabaseHas('flashcards', $data);
    }

    #[Test]
    public function it_updates_a_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Original question',
            'answer' => 'Original answer',
        ]);

        $data = [
            'question' => 'Updated question',
            'answer' => 'Updated answer',
        ];

        // Act
        $result = $this->repository->update($flashcard, $data);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => 'Updated question',
            'answer' => 'Updated answer',
        ]);
    }

    #[Test]
    public function it_deletes_a_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->delete($flashcard);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('flashcards', [
            'id' => $flashcard->id,
        ]);
    }

    #[Test]
    public function it_restores_a_deleted_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $flashcard->delete();

        // Act
        $result = $this->repository->restore($flashcard);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_force_deletes_a_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->forceDelete($flashcard);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('flashcards', [
            'id' => $flashcard->id,
        ]);
    }

    #[Test]
    public function it_restores_all_deleted_flashcards_for_user(): void
    {
        // Arrange
        Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ])->each(fn (Flashcard $flashcard) => $flashcard->delete());

        // Act
        $result = $this->repository->restoreAll($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'user_id' => $this->user->id,
            'deleted_at' => null,
        ]);
        $this->assertEquals(3, Flashcard::where('user_id', $this->user->id)->whereNull('deleted_at')->count());
    }

    #[Test]
    public function it_permanently_deletes_all_deleted_flashcards_for_user(): void
    {
        // Arrange
        Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ])->each(fn (Flashcard $flashcard) => $flashcard->delete());

        // Act
        $result = $this->repository->forceDeleteAll($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('flashcards', [
            'user_id' => $this->user->id,
        ]);
    }
}
