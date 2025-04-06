<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LogRepository $repository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LogRepository();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_gets_logs_for_user(): void
    {
        // Arrange - manually insert logs to avoid repository usage
        $now = now();
        for ($i = 0; $i < 5; $i++) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'details' => json_encode(['test' => 'data']),
                'created_at' => $now->subMinutes($i),
            ]);
        }

        // Insert logs for another user
        $otherUser = User::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            DB::table('logs')->insert([
                'user_id' => $otherUser->id,
                'action' => 'test_action',
                'details' => json_encode(['test' => 'data']),
                'created_at' => $now->subMinutes($i),
            ]);
        }

        // Act
        $result = $this->repository->getLogsForUser($this->user->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(5, $result);
        foreach ($result as $log) {
            $this->assertEquals($this->user->id, $log['user_id']);
        }
    }

    #[Test]
    public function it_gets_logs_for_user_with_custom_limit(): void
    {
        // Arrange - manually insert logs to avoid repository usage
        $now = now();
        for ($i = 0; $i < 10; $i++) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'action' => 'test_action',
                'details' => json_encode(['test' => 'data']),
                'created_at' => $now->subMinutes($i),
            ]);
        }

        // Act
        $result = $this->repository->getLogsForUser($this->user->id, 3);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    #[Test]
    public function it_logs_flashcard_creation(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardCreation($this->user->id, $flashcard);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_created', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_flashcard_update(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardUpdate($this->user->id, $flashcard);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_updated', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_flashcard_deletion(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardDeletion($this->user->id, $flashcard);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_deleted', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_flashcard_restoration(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardRestoration($this->user->id, $flashcard);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_restored', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_flashcard_practice_correctly_answered(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardPractice($this->user->id, $flashcard, true);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_answered_correctly', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_flashcard_practice_incorrectly_answered(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logFlashcardPractice($this->user->id, $flashcard, false);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('flashcard_answered_incorrectly', $result->action);
        $this->assertStringContainsString((string) $flashcard->id, $result->details);
        $this->assertStringContainsString($flashcard->question, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_study_session_start(): void
    {
        // Arrange
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $result = $this->repository->logStudySessionStart($this->user->id, $studySession);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('study_session_started', $result->action);
        $this->assertStringContainsString((string) $studySession->id, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_study_session_end(): void
    {
        // Arrange
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
            'ended_at' => now(),
        ]);

        // Act
        $result = $this->repository->logStudySessionEnd($this->user->id, $studySession);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('study_session_ended', $result->action);
        $this->assertStringContainsString((string) $studySession->id, $result->details);
        $this->assertNotNull($result->created_at);
    }

    #[Test]
    public function it_logs_practice_reset(): void
    {
        // Act
        $result = $this->repository->logPracticeReset($this->user->id);

        // Assert
        $this->assertInstanceOf(Log::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals('practice_reset', $result->action);
        $this->assertNull($result->details);
        $this->assertNotNull($result->created_at);
    }
}
