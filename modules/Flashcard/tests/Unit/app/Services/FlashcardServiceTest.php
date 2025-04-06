<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FlashcardServiceTest extends TestCase
{
    private MockInterface $flashcardRepository;

    private MockInterface $logRepository;

    private MockInterface $statisticRepository;

    private FlashcardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepository = Mockery::mock(FlashcardRepositoryInterface::class);
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        $this->service = new FlashcardService(
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
    public function it_gets_all_for_user_delegates_to_repository(): void
    {
        // Arrange
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->flashcardRepository->shouldReceive('getAllForUser')
            ->once()
            ->with(1, 15)
            ->andReturn($paginator);

        // Act
        $result = $this->service->getAllForUser(1, 15);

        // Assert
        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function it_gets_deleted_for_user_delegates_to_repository(): void
    {
        // Arrange
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->flashcardRepository->shouldReceive('getAllDeletedForUser')
            ->once()
            ->with(1, 15)
            ->andReturn($paginator);

        // Act
        $result = $this->service->getDeletedForUser(1, 15);

        // Assert
        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function it_creates_saves_flashcard_and_updates_logs_and_statistics(): void
    {
        // Arrange
        $data = ['question' => 'Test?', 'answer' => 'Answer'];
        $flashcard = new Flashcard();
        $flashcard->question = 'Test?';
        $flashcard->answer = 'Answer';
        $flashcard->user_id = 1;

        $log = new Log();
        $log->action = 'created_flashcard';

        // Expectations
        $this->flashcardRepository->shouldReceive('create')
            ->once()
            ->with(['question' => 'Test?', 'answer' => 'Answer', 'user_id' => 1])
            ->andReturn($flashcard);

        $this->logRepository->shouldReceive('logFlashcardCreation')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementFlashcardsCreated')
            ->once()
            ->with(1)
            ->andReturn(true);

        // Act
        $result = $this->service->create(1, $data);

        // Assert
        $this->assertSame($flashcard, $result);
    }

    #[Test]
    public function it_finds_for_user_delegates_to_repository(): void
    {
        // Arrange
        $flashcard = new Flashcard();
        $flashcard->id = 5;
        $flashcard->user_id = 1;

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1, false)
            ->andReturn($flashcard);

        // Act
        $result = $this->service->findForUser(1, 5);

        // Assert
        $this->assertSame($flashcard, $result);
    }

    #[Test]
    public function it_updates_returns_false_when_flashcard_not_found(): void
    {
        // Arrange
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn(null);

        // Act
        $result = $this->service->update(1, 5, ['question' => 'Updated?']);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_updates_updates_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $data = ['question' => 'Updated?'];
        $flashcard = new Flashcard();
        $flashcard->id = 5;
        $flashcard->question = 'Original?';
        $flashcard->user_id = 1;

        $log = new Log();
        $log->action = 'updated_flashcard';

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn($flashcard);

        $this->flashcardRepository->shouldReceive('update')
            ->once()
            ->with($flashcard, $data)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardUpdate')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        // Act
        $result = $this->service->update(1, 5, $data);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_deletes_returns_false_when_flashcard_not_found(): void
    {
        // Arrange
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn(null);

        // Act
        $result = $this->service->delete(1, 5);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_deleted_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = new Flashcard();
        $flashcard->id = 5;
        $flashcard->question = 'Delete me?';
        $flashcard->user_id = 1;

        $log = new Log();
        $log->action = 'deleted_flashcard';

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn($flashcard);

        $this->logRepository->shouldReceive('logFlashcardDeletion')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        $this->flashcardRepository->shouldReceive('delete')
            ->once()
            ->with($flashcard)
            ->andReturn(true);

        // Act
        $result = $this->service->delete(1, 5);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function restores_returns_false_when_flashcard_not_found(): void
    {
        // Arrange
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1, true)
            ->andReturn(null);

        // Act
        $result = $this->service->restore(1, 5);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function restore_restores_flashcard_and_logs_when_found(): void
    {
        // Arrange
        $flashcard = new Flashcard();
        $flashcard->id = 5;
        $flashcard->question = 'Restore me?';
        $flashcard->user_id = 1;

        $log = new Log();
        $log->action = 'restored_flashcard';

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1, true)
            ->andReturn($flashcard);

        $this->flashcardRepository->shouldReceive('restore')
            ->once()
            ->with($flashcard)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardRestoration')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        // Act
        $result = $this->service->restore(1, 5);

        // Assert
        $this->assertTrue($result);
    }
}
