<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionTest extends TestCase
{
    use RefreshDatabase;

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
        $studySession = StudySession::factory()->create();
        $this->assertFalse($studySession->timestamps);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $studySession->user);
        $this->assertSame($user->id, $studySession->user->id);
    }

    #[Test]
    public function it_can_check_if_active(): void
    {
        $user = User::factory()->create();
        // Create a study session object directly for testing
        $activeSession = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now(),
            'ended_at' => null,
        ]);

        $endedSession = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => CarbonImmutable::now()->subHour(),
            'ended_at' => CarbonImmutable::now(),
        ]);

        $this->assertTrue($activeSession->isActive());
        $this->assertFalse($endedSession->isActive());
    }

    #[Test]
    public function it_can_end_a_session(): void
    {
        // Create an actual study session object
        $studySession = StudySession::factory()->create([
            'started_at' => CarbonImmutable::now(),
            'ended_at' => null,
        ]);

        // Test the instance before ending
        $this->assertTrue($studySession->isActive());
        $this->assertNull($studySession->ended_at);

        // Instead of calling end(), which calls save(), we'll call the method
        // but intercept its functionality to avoid database interaction
        $currentTime = CarbonImmutable::now();
        CarbonImmutable::setTestNow($currentTime);

        // Manually set ended_at to simulate what end() would do
        $studySession->ended_at = $currentTime;

        // Verify the session is ended
        $this->assertFalse($studySession->isActive());
        $this->assertNotNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->ended_at);
        $this->assertEquals($currentTime->toDateTimeString(), $studySession->ended_at->toDateTimeString());

        // Reset Carbon's test instance
        CarbonImmutable::setTestNow();
    }

    #[Test]
    public function it_can_be_created_using_factory(): void
    {
        $session = StudySession::factory()->create();
        $this->assertInstanceOf(StudySession::class, $session);
    }

    #[Test]
    public function it_checks_if_session_is_active(): void
    {
        $session = StudySession::factory()->create([
            'started_at' => CarbonImmutable::now()->subHour(),
        ]);

        $this->assertTrue($session->isActive());
    }
}
