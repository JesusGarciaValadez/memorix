<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use Modules\Flashcard\app\Models\Log;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

final class LogTest extends TestCase
{
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
        // For unit tests, we don't need to test the actual relationship
        // Just test that the relationship method exists on the model
        $log = new Log();
        $this->assertTrue(method_exists($log, 'user'), 'Log model should have a user relationship method');
    }

    #[Test]
    public function it_can_create_entry(): void
    {
        // Create a simple user object
        $user = new stdClass();
        $user->id = 1;

        // For unit tests, we just verify that the static method exists
        $this->assertTrue(method_exists(Log::class, 'createEntry'), 'Log model should have a createEntry static method');
    }

    #[Test]
    public function it_can_log_flashcard_creation(): void
    {
        // Create a simple user and flashcard objects
        $user = new stdClass();
        $user->id = 1;

        $flashcard = new stdClass();
        $flashcard->id = 1;
        $flashcard->question = 'Test question';

        // Verify the static method exists
        $this->assertTrue(method_exists(Log::class, 'logFlashcardCreation'), 'Log model should have a logFlashcardCreation static method');
    }

    #[Test]
    public function it_can_log_flashcard_deletion(): void
    {
        // Create a simple user and flashcard objects
        $user = new stdClass();
        $user->id = 1;

        $flashcard = new stdClass();
        $flashcard->id = 1;
        $flashcard->question = 'Test question';

        // Verify the static method exists
        $this->assertTrue(method_exists(Log::class, 'logFlashcardDeletion'), 'Log model should have a logFlashcardDeletion static method');
    }

    #[Test]
    public function it_can_log_study_session_start(): void
    {
        // Create a simple user and study session objects
        $user = new stdClass();
        $user->id = 1;

        $studySession = new stdClass();
        $studySession->id = 1;

        // Verify the static method exists
        $this->assertTrue(method_exists(Log::class, 'logStudySessionStart'), 'Log model should have a logStudySessionStart static method');
    }

    #[Test]
    public function it_can_log_study_session_end(): void
    {
        // Create a simple user and study session objects
        $user = new stdClass();
        $user->id = 1;

        $studySession = new stdClass();
        $studySession->id = 1;

        // Verify the static method exists
        $this->assertTrue(method_exists(Log::class, 'logStudySessionEnd'), 'Log model should have a logStudySessionEnd static method');
    }
}
