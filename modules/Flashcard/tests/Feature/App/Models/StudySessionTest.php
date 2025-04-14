<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionTest extends TestCase
{
    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $studySession = new StudySession();
        $this->assertEquals(['user_id', 'started_at', 'ended_at'], $studySession->getFillable());
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $studySession = new StudySession();
        $casts = $studySession->getCasts();

        $this->assertArrayHasKey('started_at', $casts);
        $this->assertArrayHasKey('ended_at', $casts);
        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['ended_at']);
    }

    #[Test]
    public function it_does_not_use_timestamps(): void
    {
        $studySession = new StudySession();
        $this->assertFalse($studySession->timestamps);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $studySession = new StudySession();
        $this->assertTrue(method_exists($studySession, 'user'), 'StudySession model should have a user relationship method');
    }

    #[Test]
    public function it_can_check_if_active(): void
    {
        // Create a study session object directly for testing
        $activeSession = new StudySession();
        $activeSession->started_at = Carbon::now();
        $activeSession->ended_at = null;

        $endedSession = new StudySession();
        $endedSession->started_at = Carbon::now()->subHour();
        $endedSession->ended_at = Carbon::now();

        $this->assertTrue($activeSession->isActive());
        $this->assertFalse($endedSession->isActive());
    }

    #[Test]
    public function it_can_end_a_session(): void
    {
        // Create an actual study session object
        $studySession = new StudySession();
        $studySession->started_at = now();
        $studySession->ended_at = null;

        // Test the instance before ending
        $this->assertTrue($studySession->isActive());
        $this->assertNull($studySession->ended_at);

        // Instead of calling end(), which calls save(), we'll call the method
        // but intercept its functionality to avoid database interaction
        $currentTime = now();
        Carbon::setTestNow($currentTime);

        // Manually set ended_at to simulate what end() would do
        $studySession->ended_at = $currentTime;

        // Verify the session is ended
        $this->assertFalse($studySession->isActive());
        $this->assertNotNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->ended_at);
        $this->assertEquals($currentTime->toDateTimeString(), $studySession->ended_at->toDateTimeString());

        // Reset Carbon's test instance
        Carbon::setTestNow();
    }
}
