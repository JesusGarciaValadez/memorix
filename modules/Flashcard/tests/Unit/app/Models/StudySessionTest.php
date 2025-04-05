<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
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
        $studySession = new StudySession();
        $this->assertFalse($studySession->timestamps);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::create([
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $studySession->user);
        $this->assertEquals($user->id, $studySession->user->id);
    }

    #[Test]
    public function it_can_check_if_active(): void
    {
        // Active session (no end date)
        $activeSession = StudySession::create([
            'user_id' => User::factory()->create()->id,
            'started_at' => now(),
        ]);

        // Ended session
        $endedSession = StudySession::create([
            'user_id' => User::factory()->create()->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        $this->assertTrue($activeSession->isActive());
        $this->assertFalse($endedSession->isActive());
    }

    #[Test]
    public function it_can_end_a_session(): void
    {
        $studySession = StudySession::create([
            'user_id' => User::factory()->create()->id,
            'started_at' => now(),
        ]);

        $this->assertTrue($studySession->isActive());
        $this->assertNull($studySession->ended_at);

        $studySession->end();

        $this->assertFalse($studySession->isActive());
        $this->assertNotNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->ended_at);
    }
}
