<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\FlashcardController;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Services\FlashcardServiceInterface;

final class TestRequest extends FormRequest
{
    private array $validatedData = [];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function validated($key = null, $default = null)
    {
        return $this->validatedData;
    }

    public function setValidatedData(array $data): void
    {
        $this->validatedData = $data;
    }
}

final class FlashcardControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    private FlashcardController $controller;

    private MockInterface $flashcardService;

    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardService = Mockery::mock(FlashcardServiceInterface::class);
        $this->controller = new FlashcardController($this->flashcardService);
        $this->request = new Request();
        $this->request->setUserResolver(fn () => (object) ['id' => 1]);
    }

    public function test_index_returns_flashcards_for_user(): void
    {
        $flashcards = new LengthAwarePaginator(
            [$this->createMockFlashcard(), $this->createMockFlashcard()],
            2,
            15,
            1
        );

        $this->flashcardService
            ->shouldReceive('getAllForUser')
            ->once()
            ->with(1, 15)
            ->andReturn($flashcards);

        $response = $this->controller->index($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($flashcards->toArray(), $response->getData(true));
    }

    public function test_store_creates_flashcard(): void
    {
        $flashcard = $this->createMockFlashcard();
        $data = [
            'question' => 'Test question',
            'answer' => 'Test answer',
        ];

        $request = new TestRequest();
        $request->setValidatedData($data);
        $request->setUserResolver(fn () => (object) ['id' => 1]);

        $this->flashcardService
            ->shouldReceive('create')
            ->once()
            ->with(1, $data)
            ->andReturn($flashcard);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals(json_decode(json_encode($flashcard), true), $response->getData(true));
    }

    public function test_show_returns_flashcard(): void
    {
        $flashcard = $this->createMockFlashcard();

        $this->flashcardService
            ->shouldReceive('findForUser')
            ->once()
            ->with(1, $flashcard->id)
            ->andReturn($flashcard);

        $response = $this->controller->show($this->request, $flashcard->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_decode(json_encode($flashcard), true), $response->getData(true));
    }

    public function test_update_modifies_flashcard(): void
    {
        $flashcard = $this->createMockFlashcard();
        $data = [
            'question' => 'Updated question',
            'answer' => 'Updated answer',
        ];

        $request = new TestRequest();
        $request->setValidatedData($data);
        $request->setUserResolver(fn () => (object) ['id' => 1]);

        $this->flashcardService
            ->shouldReceive('update')
            ->once()
            ->with(1, $flashcard->id, $data)
            ->andReturn(true);

        $response = $this->controller->update($request, $flashcard->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard updated successfully'], $response->getData(true));
    }

    public function test_destroy_removes_flashcard(): void
    {
        $flashcard = $this->createMockFlashcard();

        $this->flashcardService
            ->shouldReceive('delete')
            ->once()
            ->with(1, $flashcard->id)
            ->andReturn(true);

        $response = $this->controller->destroy($this->request, $flashcard->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['message' => 'Flashcard deleted successfully'], $response->getData(true));
    }

    public function test_destroy_returns_not_found_for_nonexistent_flashcard(): void
    {
        $this->flashcardService
            ->shouldReceive('delete')
            ->once()
            ->with(1, 999)
            ->andReturn(false);

        $response = $this->controller->destroy($this->request, 999);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

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
