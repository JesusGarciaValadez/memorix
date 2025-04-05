<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Factories;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_log(): void
    {
        $log = Log::factory()->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertDatabaseHas('logs', ['id' => $log->id]);
    }

    #[Test]
    public function it_creates_a_log_with_valid_data(): void
    {
        $log = Log::factory()->create();

        $this->assertNotNull($log->user_id);
        $this->assertNotEmpty($log->action);
        $this->assertNotEmpty($log->details);
        $this->assertNotNull($log->created_at);
        $this->assertInstanceOf(CarbonInterface::class, $log->created_at);
    }

    #[Test]
    public function it_creates_a_log_with_a_real_user(): void
    {
        $user = User::factory()->create();

        $log = Log::factory()
            ->for($user)
            ->create();

        $this->assertEquals($user->id, $log->user_id);
        $this->assertInstanceOf(User::class, $log->user);
    }

    #[Test]
    public function it_can_create_a_flashcard_creation_log(): void
    {
        $log = Log::factory()
            ->flashcardCreation()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('created_flashcard', $log->action);
        $this->assertStringContainsString('Created flashcard ID:', $log->details);
        $this->assertStringContainsString('Question:', $log->details);
    }

    #[Test]
    public function it_can_create_a_flashcard_deletion_log(): void
    {
        $log = Log::factory()
            ->flashcardDeletion()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('deleted_flashcard', $log->action);
        $this->assertStringContainsString('Deleted flashcard ID:', $log->details);
        $this->assertStringContainsString('Question:', $log->details);
    }

    #[Test]
    public function it_can_create_a_study_session_start_log(): void
    {
        $log = Log::factory()
            ->studySessionStart()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('started_study_session', $log->action);
        $this->assertStringContainsString('Started study session ID:', $log->details);
    }

    #[Test]
    public function it_can_create_a_study_session_end_log(): void
    {
        $log = Log::factory()
            ->studySessionEnd()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('ended_study_session', $log->action);
        $this->assertStringContainsString('Ended study session ID:', $log->details);
    }

    #[Test]
    public function it_can_create_a_recent_log(): void
    {
        $log = Log::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(Log::class, $log);

        // A recent log should be created within the last day
        $oneDayAgo = now()->subDay();
        $this->assertTrue($log->created_at->isAfter($oneDayAgo));
    }

    #[Test]
    public function it_can_create_multiple_logs(): void
    {
        $count = 5;

        $logs = Log::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $logs);
        $this->assertDatabaseCount('logs', $count);
    }
}
