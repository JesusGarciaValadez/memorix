<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $log = Log::factory()->create(['user_id' => $user->id]);
        $expectedFillable = [
            'user_id',
            'action',
            'level',
            'description',
            'details',
        ];
        $this->assertEquals($expectedFillable, $log->getFillable());
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $log = new Log();
        $casts = $log->getCasts();

        $this->assertArrayHasKey('created_at', $casts);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    #[Test]
    public function it_does_not_use_timestamps(): void
    {
        $log = new Log();
        $this->assertFalse($log->timestamps);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $log = Log::factory()->create(['user_id' => $user->id]);
        $this->assertEquals($log->user->id, $user->id);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_can_create_entry(): void
    {
        $user = User::factory()->create();
        $log = Log::factory()->create([
            'user_id' => $user->id,
            'action' => 'Test action',
            'level' => 'info',
            'description' => 'Test description',
            'details' => json_encode(['key' => 'value'], JSON_THROW_ON_ERROR),
        ]);

        // For unit tests, we just verify that the static method exists
        $this->assertEquals($log->user_id, $user->id);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'Test action',
            'level' => 'info',
            'description' => 'Test description',
        ]);
        $this->assertDatabaseCount('logs', 1);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_can_log_flashcard_creation(): void
    {
        // Create a simple user and flashcard objects
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question',
            'answer' => 'Test answer',
            'created_at' => CarbonImmutable::now(),
        ]);
        Log::logFlashcardCreation($user, $flashcard);

        // Verify the static method exists
        $this->assertDatabaseCount('logs', 1)
            ->assertDatabaseHas('logs', [
                'user_id' => $user->id,
                'action' => 'created_flashcard',
                'level' => 'info',
                'description' => "Created flashcard ID: {$flashcard->id}, Question: Test question",
            ]);
    }

    #[Test]
    public function it_can_log_flashcard_deletion(): void
    {
        // Create a simple user and flashcard objects
        $user = User::factory()->create();

        $flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'Test question',
            'answer' => 'Test answer',
            'created_at' => CarbonImmutable::now(),
        ]);

        // Verify the static method exists
        $log = Log::logFlashcardDeletion($user, $flashcard);
        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('deleted_flashcard', $log->action);
    }

    #[Test]
    public function it_can_log_study_session_start(): void
    {
        // Create a simple user and study session objects
        $user = User::factory()->create();

        $session = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now(),
        ]);

        // Verify the static method exists
        $log = Log::logStudySessionStart($user, $session);
        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals('started_study_session', $log->action);
    }

    #[Test]
    public function it_can_log_study_session_end(): void
    {
        // Create a simple user and study session objects
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create([
            'user_id' => $user->id,
            'ended_at' => CarbonImmutable::now(),
        ]);
        Log::logStudySessionEnd($user, $studySession);

        $this->assertDatabaseCount('logs', 1)
            ->assertDatabaseHas('logs', [
                'user_id' => $user->id,
                'action' => 'ended_study_session',
                'level' => 'info',
                'description' => "Ended study session ID: {$studySession->id}",
            ]);
    }
}
