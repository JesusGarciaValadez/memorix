<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\StatisticController;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StatisticControllerTest extends TestCase
{
    private MockInterface $statisticService;

    private StatisticController $controller;

    private MockInterface $request;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticService = Mockery::mock(StatisticServiceInterface::class);
        $this->controller = new StatisticController($this->statisticService);

        // Create a real user instance
        $this->user = User::factory()->create();

        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('user')->andReturn($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_statistics_for_user(): void
    {
        // Arrange
        $expectedResponse = [
            'flashcards_created' => 0,
            'flashcards_deleted' => 0,
            'study_sessions' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
        ];

        // Expect
        $this->statisticService->shouldReceive('getStatisticsForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($expectedResponse);

        // Act
        $response = $this->controller->index($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_success_rate_for_user(): void
    {
        // Arrange
        $successRate = 0.0;
        $expectedResponse = ['success_rate' => 0.0];

        // Expect
        $this->statisticService->shouldReceive('getPracticeSuccessRate')
            ->once()
            ->with($this->user->id)
            ->andReturn($successRate);

        // Act
        $response = $this->controller->successRate($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_average_study_session_duration(): void
    {
        // Arrange
        $averageDuration = 0.0;
        $expectedResponse = ['average_duration' => 0.0];

        // Expect
        $this->statisticService->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->user->id)
            ->andReturn($averageDuration);

        // Act
        $response = $this->controller->averageDuration($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_total_study_time(): void
    {
        // Arrange
        $totalTime = 0.0;
        $expectedResponse = ['total_time' => 0.0];

        // Expect
        $this->statisticService->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->user->id)
            ->andReturn($totalTime);

        // Act
        $response = $this->controller->totalTime($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
