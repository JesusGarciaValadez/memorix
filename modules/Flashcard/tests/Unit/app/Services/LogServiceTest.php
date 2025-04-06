<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Services\LogService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class LogServiceTest extends TestCase
{
    use RefreshDatabase;

    private LogService $service;

    private LogRepositoryInterface|MockObject $logRepository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->logRepository = $this->createMock(LogRepositoryInterface::class);
        $this->service = new LogService($this->logRepository);
    }

    #[Test]
    public function it_gets_logs_for_user_delegates_to_repository(): void
    {
        // Arrange
        $userId = $this->user->id;
        $limit = 20;
        $expectedLogs = [
            ['id' => 1, 'action' => 'create_flashcard'],
            ['id' => 2, 'action' => 'start_session'],
        ];

        // Assert
        $this->logRepository->expects($this->once())
            ->method('getLogsForUser')
            ->with($userId, $limit)
            ->willReturn($expectedLogs);

        // Act
        $result = $this->service->getLogsForUser($userId, $limit);

        // Assert
        $this->assertSame($expectedLogs, $result);
    }

    #[Test]
    public function it_gets_latest_activity_delegates_to_repository(): void
    {
        // Arrange
        $userId = $this->user->id;
        $limit = 5;

        $log1 = new Log();
        $log1->id = 1;
        $log1->action = 'create_flashcard';
        $log1->details = 'Created flashcard #1';
        $log1->created_at = Carbon::parse('2023-01-01 10:00:00');

        $expectedLogs = [$log1];
        $expectedFormatted = [
            [
                'id' => 1,
                'action' => 'create_flashcard',
                'created_at' => '2023-01-01 10:00:00',
                'details' => 'Created flashcard #1',
            ],
        ];

        $this->logRepository->expects($this->once())
            ->method('getLogsForUser')
            ->with($userId, $limit)
            ->willReturn($expectedLogs);

        // Act
        $result = $this->service->getLatestActivityForUser($userId, $limit);

        // Assert
        $this->assertEquals($expectedFormatted, $result);
    }
}
