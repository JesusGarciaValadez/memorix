<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private MockInterface $logRepository;

    private MockInterface $statisticRepository;

    private FlashcardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        $this->service = new FlashcardService(
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
    public function it_gets_all_for_user(): void
    {
        // Arrange
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(); // Other user's flashcards

        // Act
        $result = $this->service->getAllForUser($this->user->id, 15);

        // Assert
        $this->assertEquals(3, $result->total());
    }

    #[Test]
    public function it_gets_deleted_for_user(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        $flashcards->each->delete();

        // Act
        $result = $this->service->getDeletedForUser($this->user->id, 15);

        // Assert
        $this->assertEquals(3, $result->total());
    }

    #[Test]
    public function it_creates_saves_flashcard_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $data = ['question' => 'Test?', 'answer' => 'Answer'];
        $log = new Log();
        $log->action = 'created_flashcard';

        // Expectations
        $this->logRepository->shouldReceive('logFlashcardCreation')
            ->once()
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementFlashcardsCreated')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        // Act
        $result = $this->service->create($this->user->id, $data);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals('Test?', $result->question);
        $this->assertEquals('Answer', $result->answer);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    #[Test]
    public function it_finds_for_user(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        // Act
        $result = $this->service->findForUser($this->user->id, $flashcard->id);

        // Assert
        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($flashcard->id, $result->id);
    }

    #[Test]
    public function it_updates_returns_false_when_flashcard_not_found(): void
    {
        // Act
        $result = $this->service->update($this->user->id, 999, ['question' => 'Updated?']);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_updates_updates_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Original?',
        ]);

        $data = ['question' => 'Updated?'];
        $log = new Log();
        $log->action = 'updated_flashcard';

        $this->logRepository->shouldReceive('logFlashcardUpdate')
            ->once()
            ->with($this->user->id, Mockery::type(Flashcard::class))
            ->andReturn($log);

        // Act
        $result = $this->service->update($this->user->id, $flashcard->id, $data);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals('Updated?', $flashcard->fresh()->question);
    }

    #[Test]
    public function it_deletes_returns_false_when_flashcard_not_found(): void
    {
        // Act
        $result = $this->service->delete($this->user->id, 999);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_deleted_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Delete me?',
        ]);

        $log = new Log();
        $log->action = 'deleted_flashcard';

        $this->logRepository->shouldReceive('logFlashcardDeletion')
            ->once()
            ->with($this->user->id, Mockery::type(Flashcard::class))
            ->andReturn($log);

        // Act
        $result = $this->service->delete($this->user->id, $flashcard->id);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted($flashcard);
    }

    #[Test]
    public function it_restores_returns_false_when_flashcard_not_found(): void
    {
        // Act
        $result = $this->service->restore($this->user->id, 999);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Restore me?',
        ]);
        $flashcard->delete();

        $log = new Log();
        $log->action = 'restored_flashcard';

        $this->logRepository->shouldReceive('logFlashcardRestoration')
            ->once()
            ->with($this->user->id, Mockery::type(Flashcard::class))
            ->andReturn($log);

        // Act
        $result = $this->service->restore($this->user->id, $flashcard->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNotSoftDeleted($flashcard);
    }

    #[Test]
    public function it_force_deletes_returns_false_when_flashcard_not_found(): void
    {
        // Act
        $result = $this->service->forceDelete($this->user->id, 999);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_force_deletes_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Force delete me?',
        ]);
        $flashcard->delete();

        $this->logRepository->shouldReceive('logFlashcardForceDelete')
            ->once()
            ->with($this->user->id, $flashcard->id, 'Force delete me?')
            ->andReturn(new Log());

        // Act
        $result = $this->service->forceDelete($this->user->id, $flashcard->id);

        // Assert
        $this->assertTrue($result);
        $this->assertModelMissing($flashcard);
    }
}
