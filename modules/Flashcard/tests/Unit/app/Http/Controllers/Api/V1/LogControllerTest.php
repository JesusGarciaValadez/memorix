<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Controllers\Api\V1;

use App\Models\User;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Http\Controllers\Api\V1\LogController;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Services\LogService;
use stdClass;
use Tests\TestCase;

final class LogControllerTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $logRepository;

    private LogController $controller;

    private MockInterface $request;

    private User $user;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);

        // Create an actual LogService instance with our mocked repository
        $logService = app()->makeWith(LogService::class, [
            'logRepository' => $this->logRepository,
        ]);

        $this->controller = new LogController($logService);

        // Create a real user instance
        $this->user = User::factory()->create();

        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('user')->andReturn($this->user);
    }

    /**
     * @throws JsonException
     */
    public function test_index_returns_logs_for_user(): void
    {
        // Arrange
        $logData = [
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'create_flashcard',
                'details' => 'Created flashcard #1',
                'created_at' => '2023-04-05 10:00:00',
                'updated_at' => '2023-04-05 10:00:00',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'action' => 'update_flashcard',
                'details' => 'Updated flashcard #1',
                'created_at' => '2023-04-05 11:00:00',
                'updated_at' => '2023-04-05 11:00:00',
            ],
        ];

        $this->request->shouldReceive('input')->with('limit', 50)->andReturn(50);

        // Expect
        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 50)
            ->andReturn($logData);

        // Act
        $response = $this->controller->index($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logData, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public function test_index_with_custom_limit(): void
    {
        // Arrange
        $logData = [
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'create_flashcard',
                'details' => 'Created flashcard #1',
                'created_at' => '2023-04-05 10:00:00',
                'updated_at' => '2023-04-05 10:00:00',
            ],
        ];

        $this->request->shouldReceive('input')->with('limit', 50)->andReturn(1);

        // Expect
        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 1)
            ->andReturn($logData);

        // Act
        $response = $this->controller->index($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($logData, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     * @throws DateMalformedStringException|DateMalformedStringException
     */
    public function test_latest_returns_latest_activity_for_user(): void
    {
        // Arrange
        $logData = [
            [
                'id' => 2,
                'user_id' => 1,
                'action' => 'update_flashcard',
                'details' => 'Updated flashcard #1',
                'created_at' => '2023-04-05 11:00:00',
                'updated_at' => '2023-04-05 11:00:00',
            ],
            [
                'id' => 1,
                'user_id' => 1,
                'action' => 'create_flashcard',
                'details' => 'Created flashcard #1',
                'created_at' => '2023-04-05 10:00:00',
                'updated_at' => '2023-04-05 10:00:00',
            ],
        ];

        $activityData = [
            [
                'id' => 2,
                'action' => 'update_flashcard',
                'created_at' => '2023-04-05 11:00:00',
                'details' => 'Updated flashcard #1',
            ],
            [
                'id' => 1,
                'action' => 'create_flashcard',
                'created_at' => '2023-04-05 10:00:00',
                'details' => 'Created flashcard #1',
            ],
        ];

        $this->request->shouldReceive('input')->with('limit', 10)->andReturn(10);

        // Create log objects with the format expected by getLatestActivityForUser
        $logs = [];
        foreach ($logData as $data) {
            $log = new stdClass();
            $log->id = $data['id'];
            $log->action = $data['action'];
            $log->details = $data['details'];
            $log->created_at = new DateTimeImmutable($data['created_at']);
            $logs[] = $log;
        }

        // Expect
        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 10)
            ->andReturn($logs);

        // Act
        $response = $this->controller->latest($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($activityData, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedStringException
     * @throws JsonException
     */
    public function test_latest_with_custom_limit(): void
    {
        // Arrange
        $logData = [
            [
                'id' => 2,
                'user_id' => 1,
                'action' => 'update_flashcard',
                'details' => 'Updated flashcard #1',
                'created_at' => '2023-04-05 11:00:00',
                'updated_at' => '2023-04-05 11:00:00',
            ],
        ];

        $activityData = [
            [
                'id' => 2,
                'action' => 'update_flashcard',
                'created_at' => '2023-04-05 11:00:00',
                'details' => 'Updated flashcard #1',
            ],
        ];

        $this->request->shouldReceive('input')->with('limit', 10)->andReturn(1);

        // Create log objects with the format expected by getLatestActivityForUser
        $logs = [];
        foreach ($logData as $data) {
            $log = new stdClass();
            $log->id = $data['id'];
            $log->action = $data['action'];
            $log->details = $data['details'];
            $log->created_at = new DateTimeImmutable((string) $data['created_at']);
            $logs[] = $log;
        }

        // Expect
        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 1)
            ->andReturn($logs);

        // Act
        $response = $this->controller->latest($this->request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($activityData, json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }
}
