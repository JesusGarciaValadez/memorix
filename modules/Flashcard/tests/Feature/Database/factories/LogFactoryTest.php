<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\factories;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_log(): void
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
        $this->assertNotEmpty($log->description);
        $this->assertEmpty($log->details);
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
    public function it_creates_a_flashcard_creation_log(): void
    {
        $log = Log::factory()
            ->flashcardCreation()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('created_flashcard', $log->action);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('Created flashcard ID:', $log->description);
        $this->assertStringContainsString('Question:', $log->description);
        $this->assertNull($log->details);
    }

    #[Test]
    public function it_creates_a_flashcard_deletion_log(): void
    {
        $log = Log::factory()
            ->flashcardDeletion()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('deleted_flashcard', $log->action);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('Deleted flashcard ID:', $log->description);
        $this->assertStringContainsString('Question:', $log->description);
        $this->assertNotNull($log->details, 'Log details should not be null for flashcard deletion.');
        $this->assertArrayHasKey('flashcard_id', $log->details->toArray());
    }

    #[Test]
    public function it_creates_a_study_session_start_log(): void
    {
        $log = Log::factory()
            ->studySessionStart()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('started_study_session', $log->action);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('Started study session ID:', $log->description);
    }

    #[Test]
    public function it_creates_a_study_session_end_log(): void
    {
        $log = Log::factory()
            ->studySessionEnd()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('ended_study_session', $log->action);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('Ended study session ID:', $log->description);
    }

    #[Test]
    public function it_creates_a_recent_log(): void
    {
        $log = Log::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(Log::class, $log);
        $this->assertNotNull($log->created_at);

        // A recent log should be created within the last day
        $oneDayAgo = now()->subDay();
        $this->assertTrue($log->created_at->isAfter($oneDayAgo));
    }

    #[Test]
    public function it_creates_multiple_logs(): void
    {
        $count = 5;

        $logs = Log::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $logs);
        $this->assertDatabaseCount('logs', $count);
    }

    #[Test]
    public function it_can_set_log_level_info(): void
    {
        $log = Log::factory()->create([
            'level' => Log::LEVEL_INFO,
            'description' => 'This is an information log.',
        ]);
        $this->assertSame(Log::LEVEL_INFO, $log->level);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('information', $log->description);
    }

    #[Test]
    public function it_can_set_log_level_warning(): void
    {
        $log = Log::factory()->create([
            'level' => Log::LEVEL_WARNING,
            'description' => 'This is a warning log.',
        ]);
        $this->assertSame(Log::LEVEL_WARNING, $log->level);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('warning', $log->description);
    }

    #[Test]
    public function it_can_set_log_level_error(): void
    {
        $log = Log::factory()->create([
            'level' => Log::LEVEL_ERROR,
            'description' => 'This is an error log.',
        ]);
        $this->assertSame(Log::LEVEL_ERROR, $log->level);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('error', $log->description);
    }

    #[Test]
    public function it_can_set_log_level_debug(): void
    {
        $log = Log::factory()->create([
            'level' => Log::LEVEL_DEBUG,
            'description' => 'This is a debug log.',
        ]);
        $this->assertSame(Log::LEVEL_DEBUG, $log->level);
        $this->assertNotNull($log->description);
        $this->assertStringContainsString('debug', $log->description);
    }

    #[Test]
    public function it_can_set_details(): void
    {
        $details = ['key' => 'value', 'nested' => ['a' => 1]];
        $log = Log::factory()->create([
            'details' => $details,
        ]);

        $this->assertNotNull($log->details);
        $this->assertEquals($details, ($log->details)->toArray());
    }

    #[Test]
    public function it_can_set_message(): void
    {
        $message = 'Custom log message';
        $log = Log::factory()->create([
            'description' => $message,
        ]);
        $this->assertSame($message, $log->description);
    }

    #[Test]
    public function created_at_should_be_recent(): void
    {
        $log = Log::factory()->create([
            'created_at' => CarbonImmutable::now(),
        ]);
        $this->assertNotNull($log->created_at);
        $this->assertTrue($log->created_at->isAfter(now()->subMinute()));
    }
}
