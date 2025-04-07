<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $log = new Log();
        $expectedFillable = [
            'user_id',
            'action',
            'level',
            'details',
            'created_at',
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
        $log = Log::create([
            'user_id' => $user->id,
            'action' => 'test_action',
            'created_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    #[Test]
    public function it_can_create_entry(): void
    {
        $user = User::factory()->create();
        $log = Log::createEntry($user, 'test_action', Log::LEVEL_INFO, 'Test details');

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('test_action', $log->action);
        $this->assertEquals('Test details', $log->details);
        $this->assertNotNull($log->created_at);
    }

    #[Test]
    public function it_can_log_flashcard_creation(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::create([
            'user_id' => $user->id,
            'question' => 'Test question',
            'answer' => 'Test answer',
        ]);

        $log = Log::logFlashcardCreation($user, $flashcard);

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('created_flashcard', $log->action);
        $this->assertStringContainsString('Created flashcard ID: '.$flashcard->id, $log->details);
        $this->assertStringContainsString($flashcard->question, $log->details);
    }

    #[Test]
    public function it_can_log_flashcard_deletion(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create();

        $log = Log::logFlashcardDeletion($user, $flashcard);

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('deleted_flashcard', $log->action);
        $this->assertStringContainsString('Deleted flashcard ID: '.$flashcard->id, $log->details);
        $this->assertStringContainsString($flashcard->question, $log->details);
    }

    #[Test]
    public function it_can_log_study_session_start(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create();

        $log = Log::logStudySessionStart($user, $studySession);

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('started_study_session', $log->action);
        $this->assertStringContainsString('Started study session ID: '.$studySession->id, $log->details);
    }

    #[Test]
    public function it_can_log_study_session_end(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create();

        $log = Log::logStudySessionEnd($user, $studySession);

        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('ended_study_session', $log->action);
        $this->assertStringContainsString('Ended study session ID: '.$studySession->id, $log->details);
    }
}
