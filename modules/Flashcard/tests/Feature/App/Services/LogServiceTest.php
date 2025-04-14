<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use Mockery;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Services\LogService;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

final class LogServiceTest extends TestCase
{
    private LogService $service;

    private $logRepository;

    private stdClass $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createTestUser();
        $this->logRepository = Mockery::mock(LogRepositoryInterface::class);
        $this->service = new LogService($this->logRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_gets_logs_for_user(): void
    {
        // Arrange
        $logs = [
            ['id' => 1, 'action' => 'test_action', 'level' => 'info', 'created_at' => now()],
            ['id' => 2, 'action' => 'test_action', 'level' => 'info', 'created_at' => now()],
        ];

        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 50)
            ->andReturn($logs);

        // Act
        $result = $this->service->getLogsForUser($this->user->id, 50);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('test_action', $result[0]['action']);
        $this->assertEquals('info', $result[0]['level']);
    }

    #[Test]
    public function it_gets_latest_activity_for_user(): void
    {
        // Arrange
        $logs = [
            ['id' => 1, 'action' => 'test_action', 'level' => 'info', 'created_at' => now()],
            ['id' => 2, 'action' => 'test_action', 'level' => 'info', 'created_at' => now()],
        ];

        $this->logRepository->shouldReceive('getLogsForUser')
            ->once()
            ->with($this->user->id, 10)
            ->andReturn($logs);

        // Act
        $result = $this->service->getLatestActivityForUser($this->user->id, 10);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('test_action', $result[0]['action']);
        $this->assertEquals('info', $result[0]['level']);
    }

    /**
     * Create a test user object
     */
    private function createTestUser(int $id = 1): stdClass
    {
        $user = new stdClass();
        $user->id = $id;
        $user->name = 'Test User';

        return $user;
    }
}
