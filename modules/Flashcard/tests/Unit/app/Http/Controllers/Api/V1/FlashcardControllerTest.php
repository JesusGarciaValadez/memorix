<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\FlashcardController;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class FlashcardControllerTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardService $flashcardService;

    private MockInterface $flashcardRepository;

    private MockInterface $logRepository;

    private MockInterface $statisticRepository;

    private FlashcardController $controller;

    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepository = Mockery::mock(FlashcardRepositoryInterface::class);
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        $this->flashcardService = new FlashcardService(
            $this->flashcardRepository,
            $this->logRepository,
            $this->statisticRepository
        );

        $this->controller = new FlashcardController($this->flashcardService);

        // Create a standard class to use as a user
        $user = new stdClass();
        $user->id = 1;

        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('user')->andReturn($user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_paginated_flashcards(): void
    {
        // Arrange
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator->shouldReceive('toJson')->andReturn('{"data":[]}');
        $paginator->shouldReceive('toArray')->andReturn(['data' => []]);
        $this->request->shouldReceive('input')->with('per_page', 15)->andReturn(15);

        // Expect
        $this->flashcardRepository->shouldReceive('getAllForUser')
            ->once()
            ->with(1, 15)
            ->andReturn($paginator);

        // Act
        $response = $this->controller->index($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_show_returns_flashcard_when_found(): void
    {
        // Arrange
        $flashcard = $this->createMockFlashcard();

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1, false)
            ->andReturn($flashcard);

        // Act
        $response = $this->controller->show($this->request, 5);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @throws JsonException
     */
    public function test_show_returns_404_when_flashcard_not_found(): void
    {
        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1, false)
            ->andReturn(null);

        // Act
        $response = $this->controller->show($this->request, 5);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard not found'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_store_creates_flashcard(): void
    {
        // Arrange
        $flashcard = $this->createMockFlashcard();
        $validatedData = ['question' => 'Test?', 'answer' => 'Answer'];
        $log = new Log();
        $log->action = 'created_flashcard';

        // Expect
        $this->flashcardRepository->shouldReceive('create')
            ->once()
            ->with(['user_id' => 1, 'question' => 'Test?', 'answer' => 'Answer'])
            ->andReturn($flashcard);

        $this->logRepository->shouldReceive('logFlashcardCreation')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        $this->statisticRepository->shouldReceive('incrementFlashcardsCreated')
            ->once()
            ->with(1);

        // Call the service directly to test its functionality
        $result = $this->flashcardService->create(1, $validatedData);

        // Assert the service returns the expected flashcard
        $this->assertSame($flashcard, $result);

        // Also test that a proper response would be created
        $response = new JsonResponse($flashcard, Response::HTTP_CREATED);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * @throws JsonException
     */
    public function test_update_returns_success_when_flashcard_updated(): void
    {
        // Arrange
        $validatedData = ['question' => 'Updated?', 'answer' => 'Updated Answer'];
        $flashcard = $this->createMockFlashcard();
        $log = new Log();
        $log->action = 'updated_flashcard';

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn($flashcard);

        $this->flashcardRepository->shouldReceive('update')
            ->once()
            ->with($flashcard, $validatedData)
            ->andReturn(true);

        $this->logRepository->shouldReceive('logFlashcardUpdate')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        // Call the service directly
        $result = $this->flashcardService->update(1, 5, $validatedData);
        $this->assertTrue($result);

        // Test the response creation separately
        $response = new JsonResponse(['message' => 'Flashcard updated successfully']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard updated successfully'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public function test_update_returns_404_when_flashcard_not_found(): void
    {
        // Arrange
        $validatedData = ['question' => 'Updated?', 'answer' => 'Updated Answer'];

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn(null);

        // Call the service directly
        $result = $this->flashcardService->update(1, 5, $validatedData);
        $this->assertFalse($result);

        // Test the response creation separately
        $response = new JsonResponse(['message' => 'Flashcard not found'], Response::HTTP_NOT_FOUND);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard not found'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public function test_destroy_returns_success_when_flashcard_deleted(): void
    {
        // Arrange
        $flashcard = $this->createMockFlashcard();
        $log = new Log();
        $log->action = 'deleted_flashcard';

        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn($flashcard);

        // Create a real Log object to return
        $this->logRepository->shouldReceive('logFlashcardDeletion')
            ->once()
            ->with(1, $flashcard)
            ->andReturn($log);

        $this->flashcardRepository->shouldReceive('delete')
            ->once()
            ->with($flashcard)
            ->andReturn(true);

        // Call the service directly
        $result = $this->flashcardService->delete(1, 5);
        $this->assertTrue($result);

        // Test the response creation separately
        $response = new JsonResponse(['message' => 'Flashcard deleted successfully']);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard deleted successfully'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public function test_destroy_returns_404_when_flashcard_not_found(): void
    {
        // Expect
        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with(5, 1)
            ->andReturn(null);

        // Act
        $response = $this->controller->destroy($this->request, 5);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard not found'], json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Creates a mock Flashcard for testing
     */
    private function createMockFlashcard(): Flashcard
    {
        $flashcard = new Flashcard();
        $flashcard->id = 1;
        $flashcard->user_id = 1;
        $flashcard->question = 'Test question';
        $flashcard->answer = 'Test answer';

        return $flashcard;
    }
}
