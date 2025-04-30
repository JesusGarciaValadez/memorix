<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Services\LogService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogServiceTest extends TestCase
{
    use RefreshDatabase;

    private LogService $service;

    private LogRepositoryInterface|Mockery\MockInterface $logRepository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createTestUser();
        $realRepository = resolve(LogRepositoryInterface::class);
        $this->service = new LogService($realRepository);
        $this->logRepository = Mockery::spy($realRepository);
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

        // @phpstan-ignore-next-line
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

        // @phpstan-ignore-next-line}
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
    private function createTestUser(int $id = 1): User
    {
        return User::factory()->create([
            'id' => $id,
            'name' => 'Test User',
        ]);
    }
}
