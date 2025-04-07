<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\StatisticController;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticControllerTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $statisticRepository;

    private StatisticController $controller;

    private MockInterface $request;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);

        // Create a real StatisticService with the mocked repository
        $statisticService = new StatisticService($this->statisticRepository);

        $this->controller = new StatisticController($statisticService);

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
        $statistic = new Statistic();
        $statistic->total_flashcards = 10;
        $statistic->total_study_sessions = 5;
        $statistic->total_correct_answers = 30;
        $statistic->total_incorrect_answers = 10;

        $expectedResponse = [
            'flashcards_created' => 10,
            'flashcards_deleted' => 0,
            'study_sessions' => 5,
            'correct_answers' => 30,
            'incorrect_answers' => 10,
        ];

        // Expect
        $this->statisticRepository->shouldReceive('getForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($statistic);

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
        $statistic = new Statistic();
        $statistic->total_correct_answers = 75;
        $statistic->total_incorrect_answers = 25;

        $expectedResponse = ['success_rate' => 75.0];

        // Expect
        $this->statisticRepository->shouldReceive('getForUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($statistic);

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
        $averageDuration = 25.5;
        $expectedResponse = ['average_duration' => 25.5];

        // Expect
        $this->statisticRepository->shouldReceive('getAverageStudySessionDuration')
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
        $totalTime = 120.75;
        $expectedResponse = ['total_time' => 120.75];

        // Expect
        $this->statisticRepository->shouldReceive('getTotalStudyTime')
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
