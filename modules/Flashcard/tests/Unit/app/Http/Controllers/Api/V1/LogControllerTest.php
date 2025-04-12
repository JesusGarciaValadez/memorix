<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Flashcard\app\Http\Controllers\Api\V1\LogController;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\Tests\TestCase;

final class LogControllerTest extends TestCase
{
    private LogController $controller;

    private LogService $service;

    private User $user;

    private LogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->repository = new LogRepository();
        $this->service = new LogService($this->repository);
        $this->controller = new LogController($this->service);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_logs_for_user(): void
    {
        // Arrange
        $logs = Log::factory()->count(2)->create(['user_id' => $this->user->id]);
        $request = Request::create('/api/logs');
        $request->setUserResolver(fn () => $this->user);

        // Act
        $response = $this->controller->index($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $data = json_decode($response->content(), true);
        $this->assertCount(2, $data);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_logs_with_custom_limit(): void
    {
        // Arrange
        Log::factory()->count(5)->create(['user_id' => $this->user->id]);
        $request = Request::create('/api/logs', 'GET', ['limit' => 3]);
        $request->setUserResolver(fn () => $this->user);

        // Act
        $response = $this->controller->index($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $data = json_decode($response->content(), true);
        $this->assertCount(3, $data);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_latest_activity_for_user(): void
    {
        // Arrange
        $logs = Log::factory()->count(2)->create(['user_id' => $this->user->id]);
        $request = Request::create('/api/logs/latest');
        $request->setUserResolver(fn () => $this->user);

        // Act
        $response = $this->controller->latest($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $data = json_decode($response->content(), true);
        $this->assertCount(2, $data);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_latest_activity_with_custom_limit(): void
    {
        // Arrange
        Log::factory()->count(5)->create(['user_id' => $this->user->id]);
        $request = Request::create('/api/logs/latest', 'GET', ['limit' => 3]);
        $request->setUserResolver(fn () => $this->user);

        // Act
        $response = $this->controller->latest($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $data = json_decode($response->content(), true);
        $this->assertCount(3, $data);
    }
}
