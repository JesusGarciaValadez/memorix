<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\Tests\TestCase;

final class LogServiceTest extends TestCase
{
    private LogService $service;

    private User $user;

    private LogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->repository = new LogRepository();
        $this->service = new LogService($this->repository);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_logs_for_user(): void
    {
        // Arrange
        $now = now();
        $logs = [];
        for ($i = 0; $i < 2; $i++) {
            $logs[] = Log::factory()->create([
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'level' => Log::LEVEL_INFO,
                'created_at' => $now->copy()->subMinutes($i),
            ]);
        }

        // Act
        $result = $this->service->getLogsForUser($this->user->id, 50);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($logs[0]->action, $result[0]['action']);
        $this->assertEquals($logs[0]->level, $result[0]['level']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_latest_activity_for_user(): void
    {
        // Arrange
        $now = now();
        $logs = [];
        for ($i = 0; $i < 2; $i++) {
            $logs[] = Log::factory()->create([
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'level' => Log::LEVEL_INFO,
                'created_at' => $now->copy()->subMinutes($i),
            ]);
        }

        // Act
        $result = $this->service->getLatestActivityForUser($this->user->id, 50);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($logs[0]->action, $result[0]['action']);
        $this->assertEquals($logs[0]->level, $result[0]['level']);
        $this->assertEquals($logs[1]->action, $result[1]['action']);
        $this->assertEquals($logs[1]->level, $result[1]['level']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_user_login(): void
    {
        // Act
        $log = $this->repository->logUserLogin($this->user->id);

        // Assert
        $this->assertEquals('user_login', $log->action);
        $this->assertEquals(Log::LEVEL_INFO, $log->level);
        $this->assertStringContainsString($this->user->name, $log->details);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_flashcard_list_view(): void
    {
        // Act
        $log = $this->repository->logFlashcardList($this->user->id);

        // Assert
        $this->assertEquals('viewed_flashcard_list', $log->action);
        $this->assertEquals(Log::LEVEL_DEBUG, $log->level);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_flashcard_practice_answer(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act - Test correct answer
        $correctLog = $this->repository->logFlashcardPractice($this->user->id, $flashcard, true);

        // Assert correct answer
        $this->assertEquals('flashcard_answered_correctly', $correctLog->action);
        $this->assertEquals(Log::LEVEL_INFO, $correctLog->level);
        $this->assertStringContainsString('Correct', $correctLog->details);

        // Act - Test incorrect answer
        $incorrectLog = $this->repository->logFlashcardPractice($this->user->id, $flashcard, false);

        // Assert incorrect answer
        $this->assertEquals('flashcard_answered_incorrectly', $incorrectLog->action);
        $this->assertEquals(Log::LEVEL_WARNING, $incorrectLog->level);
        $this->assertStringContainsString('Incorrect', $incorrectLog->details);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_statistics_view(): void
    {
        // Act
        $log = $this->repository->logStatisticsView($this->user->id);

        // Assert
        $this->assertEquals('statistics_viewed', $log->action);
        $this->assertEquals(Log::LEVEL_DEBUG, $log->level);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_practice_reset(): void
    {
        // Act
        $log = $this->repository->logPracticeReset($this->user->id);

        // Assert
        $this->assertEquals('practice_reset', $log->action);
        $this->assertEquals(Log::LEVEL_WARNING, $log->level);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_user_exit(): void
    {
        // Act
        $log = $this->repository->logUserExit($this->user->id);

        // Assert
        $this->assertEquals('user_exit', $log->action);
        $this->assertEquals(Log::LEVEL_INFO, $log->level);
        $this->assertStringContainsString($this->user->name, $log->details);
    }
}
