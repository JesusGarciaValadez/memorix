<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\FlashcardServiceInterface;
use Modules\Flashcard\app\Services\LogServiceInterface;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardServiceTest extends TestCase
{
    use RefreshDatabase; // Use RefreshDatabase to handle DB state

    private FlashcardServiceInterface $service;

    private LogServiceInterface|Mockery\MockInterface $logServiceMock;

    private StatisticServiceInterface|Mockery\MockInterface $statisticServiceMock;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create mocks for dependencies
        $this->logServiceMock = Mockery::mock(LogServiceInterface::class);
        $this->statisticServiceMock = Mockery::mock(StatisticServiceInterface::class);
        /** @var StatisticServiceInterface&Mockery\MockInterface $statisticServiceMock */

        // Instantiate the service with mocks
        $this->service = new FlashcardService(
            // @phpstan-ignore-next-line argument.type
            $this->logServiceMock,
            // @phpstan-ignore-next-line argument.type
            $this->statisticServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_gets_all_for_user(): void
    {
        // Arrange: Create some flashcards for the user
        Flashcard::factory()->count(3)->for($this->user)->create();
        Flashcard::factory()->count(2)->create(); // Create flashcards for another user

        // Act
        $result = $this->service->getAllForUser($this->user->id, 10);

        // Assert
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items()); // Check only user's flashcards are returned
        foreach ($result->items() as $flashcard) {
            /** @var Flashcard $flashcard */
            $this->assertEquals($this->user->id, $flashcard->user_id);
        }
    }

    #[Test]
    public function it_gets_deleted_for_user(): void
    {
        // Arrange: Create and soft-delete flashcards
        $deletedFlashcards = Flashcard::factory()->count(2)->for($this->user)->create();
        foreach ($deletedFlashcards as $flashcard) {
            $flashcard->delete();
        }
        Flashcard::factory()->count(1)->for($this->user)->create(); // Non-deleted
        Flashcard::factory()->count(1)->create(['deleted_at' => now()]); // Deleted for another user

        // Act
        $result = $this->service->getDeletedForUser($this->user->id, 10);

        // Assert
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items()); // Check only user's deleted flashcards are returned
        foreach ($result->items() as $flashcard) {
            /** @var Flashcard $flashcard */
            $this->assertEquals($this->user->id, $flashcard->user_id);
            $this->assertNotNull($flashcard->deleted_at);
        }
    }

    #[Test]
    public function it_creates_flashcard_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $flashcardData = [
            'question' => 'What is PHP?',
            'answer' => 'A server-side scripting language',
        ];

        // Set expectations for the mocks
        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logFlashcardCreation')
            ->once() // Expect the method to be called once
            ->withArgs(fn (int $userId, Flashcard $flashcard): bool =>
                // Check if the correct user ID and flashcard data are passed
                $userId === $this->user->id &&
                   $flashcard->question === $flashcardData['question'] &&
                   $flashcard->answer === $flashcardData['answer']);

        // @phpstan-ignore-next-line
        $this->statisticServiceMock
            ->shouldReceive('incrementTotalFlashcards')
            ->once() // Expect the method to be called once
            ->with($this->user->id); // Expect it to be called with the correct user ID

        // Act
        $createdFlashcard = $this->service->create($this->user->id, $flashcardData);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $createdFlashcard);
        $this->assertEquals($this->user->id, $createdFlashcard->user_id);
        $this->assertEquals($flashcardData['question'], $createdFlashcard->question);
        $this->assertEquals($flashcardData['answer'], $createdFlashcard->answer);

        // Verify that the flashcard exists in the database
        $this->assertDatabaseHas('flashcards', [
            'id' => $createdFlashcard->id,
            'user_id' => $this->user->id,
            'question' => $flashcardData['question'],
            'answer' => $flashcardData['answer'],
        ]);

        // Mockery expectations are automatically verified in tearDown
    }

    #[Test]
    public function it_finds_for_user(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->for($this->user)->create();
        $otherFlashcard = Flashcard::factory()->create();

        // Act
        $foundFlashcard = $this->service->findForUser($this->user->id, $flashcard->id);
        $notFoundFlashcard = $this->service->findForUser($this->user->id, $otherFlashcard->id);
        $foundDeleted = $this->service->findForUser($this->user->id, $flashcard->id, true); // Test withTrashed

        // Assert
        $this->assertInstanceOf(Flashcard::class, $foundFlashcard);
        $this->assertEquals($flashcard->id, $foundFlashcard->id);
        $this->assertNull($notFoundFlashcard);
        $this->assertInstanceOf(Flashcard::class, $foundDeleted); // Should find even if not deleted when withTrashed=true

        // Test finding deleted
        $flashcard->delete();
        $foundDeletedAfter = $this->service->findForUser($this->user->id, $flashcard->id, true);
        $notFoundDeleted = $this->service->findForUser($this->user->id, $flashcard->id, false);
        $this->assertInstanceOf(Flashcard::class, $foundDeletedAfter);
        $this->assertNull($notFoundDeleted);
    }

    #[Test]
    public function it_updates_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->for($this->user)->create([
            'question' => 'Old Question?',
        ]);
        $updateData = ['question' => 'New Question?'];

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logFlashcardUpdate')
            ->once()
            ->with($this->user->id, Mockery::on(fn ($arg): bool => $arg instanceof Flashcard && $arg->id === $flashcard->id));

        // Act
        $result = $this->service->update($this->user->id, $flashcard->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => 'New Question?',
        ]);
    }

    #[Test]
    public function it_returns_false_when_updating_non_existent_flashcard(): void
    {
        // Arrange
        $nonExistentId = 999;
        $updateData = ['question' => 'New Question?'];
        // @phpstan-ignore-next-line
        $this->logServiceMock->shouldNotReceive('logFlashcardUpdate'); // Log should not be called

        // Act
        $result = $this->service->update($this->user->id, $nonExistentId, $updateData);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->for($this->user)->create();

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logFlashcardDeletion')
            ->once()
            ->with($this->user->id, Mockery::on(fn ($arg): bool => $arg instanceof Flashcard && $arg->id === $flashcard->id));

        // Act
        $result = $this->service->delete($this->user->id, $flashcard->id);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('flashcards', ['id' => $flashcard->id]);
    }

    #[Test]
    public function it_returns_false_when_deleting_non_existent_flashcard(): void
    {
        // Arrange
        $nonExistentId = 999;
        // @phpstan-ignore-next-line
        $this->logServiceMock->shouldNotReceive('logFlashcardDeletion'); // Log should not be called

        // Act
        $result = $this->service->delete($this->user->id, $nonExistentId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->for($this->user)->create();
        $flashcard->delete(); // Soft delete it first
        $this->assertSoftDeleted('flashcards', ['id' => $flashcard->id]);

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logFlashcardRestoration')
            ->once()
            ->with($this->user->id, Mockery::on(fn ($arg): bool => $arg instanceof Flashcard && $arg->id === $flashcard->id));

        // Act
        $result = $this->service->restore($this->user->id, $flashcard->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', ['id' => $flashcard->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_returns_false_when_restoring_non_existent_or_not_deleted_flashcard(): void
    {
        // Arrange
        $nonExistentId = 999;
        $notDeletedFlashcard = Flashcard::factory()->for($this->user)->create();
        // @phpstan-ignore-next-line
        $this->logServiceMock->shouldNotReceive('logFlashcardRestoration'); // Log should not be called

        // Act
        $resultNonExistent = $this->service->restore($this->user->id, $nonExistentId);
        $resultNotDeleted = $this->service->restore($this->user->id, $notDeletedFlashcard->id);

        // Assert
        $this->assertFalse($resultNonExistent);
        $this->assertFalse($resultNotDeleted); // Should also return false if not deleted
    }

    #[Test]
    public function it_force_deletes_flashcard(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->for($this->user)->create();
        $flashcardId = $flashcard->id;
        $flashcardQuestion = $flashcard->question; // Store before deletion

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logFlashcardForceDelete')
            ->once()
            ->with($this->user->id, $flashcardId, $flashcardQuestion);

        // Act
        $result = $this->service->forceDelete($this->user->id, $flashcardId);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('flashcards', ['id' => $flashcardId]);
    }

    #[Test]
    public function it_returns_false_when_force_deleting_non_existent_flashcard(): void
    {
        // Arrange
        $nonExistentId = 999;
        // @phpstan-ignore-next-line
        $this->logServiceMock->shouldNotReceive('logFlashcardForceDelete'); // Log should not be called

        // Act
        $result = $this->service->forceDelete($this->user->id, $nonExistentId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_all_deleted_flashcards_for_user(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(3)->for($this->user)->create();
        foreach ($flashcards as $flashcard) {
            $flashcard->delete();
        }
        $this->assertCount(3, Flashcard::onlyTrashed()->where('user_id', $this->user->id)->get());

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logAllFlashcardsRestore')
            ->once()
            ->with($this->user->id);

        // Act
        $result = $this->service->restoreAllForUser($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, Flashcard::onlyTrashed()->where('user_id', $this->user->id)->get());
        $this->assertCount(3, Flashcard::where('user_id', $this->user->id)->get());
    }

    #[Test]
    public function it_force_deletes_all_deleted_flashcards_for_user(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(3)->for($this->user)->create();
        foreach ($flashcards as $flashcard) {
            $flashcard->delete();
        }
        Flashcard::factory()->count(1)->create(['deleted_at' => now()]); // Trashed for another user
        $this->assertCount(3, Flashcard::onlyTrashed()->where('user_id', $this->user->id)->get());

        // @phpstan-ignore-next-line
        $this->logServiceMock
            ->shouldReceive('logAllFlashcardsPermanentDelete')
            ->once()
            ->with($this->user->id);

        // Act
        $result = $this->service->forceDeleteAllForUser($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseCount('flashcards', 1); // Only the other user's trashed card remains
        $this->assertCount(0, Flashcard::withTrashed()->where('user_id', $this->user->id)->get());
    }
}
