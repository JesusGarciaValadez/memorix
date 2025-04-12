<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Factories;

use App\Models\User;
use Carbon\CarbonInterface;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StudySessionFactoryTest extends TestCase
{
    #[Test]
    public function it_can_create_a_study_session(): void
    {
        $studySession = StudySession::factory()->create();

        $this->assertInstanceOf(StudySession::class, $studySession);
        $this->assertDatabaseHas('study_sessions', ['id' => $studySession->id]);
    }

    #[Test]
    public function it_creates_a_study_session_with_valid_data(): void
    {
        $studySession = StudySession::factory()->create();

        $this->assertNotNull($studySession->user_id);
        $this->assertNotNull($studySession->started_at);
        $this->assertNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->started_at);
    }

    #[Test]
    public function it_creates_a_study_session_with_a_real_user(): void
    {
        $user = User::factory()->create();

        $studySession = StudySession::factory()
            ->for($user)
            ->create();

        $this->assertEquals($user->id, $studySession->user_id);
        $this->assertInstanceOf(User::class, $studySession->user);
    }

    #[Test]
    public function it_can_create_a_completed_study_session(): void
    {
        $studySession = StudySession::factory()
            ->completed()
            ->create();

        $this->assertInstanceOf(StudySession::class, $studySession);
        $this->assertNotNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->ended_at);

        // The ended_at date should be after the started_at date
        $this->assertTrue($studySession->ended_at->isAfter($studySession->started_at));
    }

    #[Test]
    public function it_can_create_a_recent_study_session(): void
    {
        $studySession = StudySession::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(StudySession::class, $studySession);

        // A recent study session should be started within the last 2 days
        $twoDaysAgo = now()->subDays(2);
        $this->assertTrue($studySession->started_at->isAfter($twoDaysAgo));
    }

    #[Test]
    public function it_can_create_a_short_study_session(): void
    {
        $studySession = StudySession::factory()
            ->shortSession()
            ->create();

        $this->assertInstanceOf(StudySession::class, $studySession);
        $this->assertNotNull($studySession->ended_at);

        // Calculate the duration in minutes
        $durationInMinutes = $studySession->started_at->diffInMinutes($studySession->ended_at);

        // A short session should be less than or equal to 10 minutes
        $this->assertLessThanOrEqual(10, $durationInMinutes);
    }

    #[Test]
    public function it_can_create_multiple_study_sessions(): void
    {
        $count = 3;

        $studySessions = StudySession::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $studySessions);
        $this->assertDatabaseCount('study_sessions', $count);
    }
}
