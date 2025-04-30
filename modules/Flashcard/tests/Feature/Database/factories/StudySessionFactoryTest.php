<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\factories;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionFactoryTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertNotNull($studySession->ended_at);
        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $studySession->started_at;
        $this->assertInstanceOf(CarbonInterface::class, $startedAt);
        $this->assertTrue($startedAt->isAfter(Carbon::now()->subYears(1)));
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
        $this->assertNotNull($studySession->started_at);
        $this->assertNotNull($studySession->ended_at);
        $this->assertInstanceOf(CarbonInterface::class, $studySession->ended_at);

        /** @var Carbon|CarbonImmutable $endedAt */
        $endedAt = $studySession->ended_at;
        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $studySession->started_at;
        $this->assertInstanceOf(CarbonInterface::class, $endedAt);
        $this->assertInstanceOf(CarbonInterface::class, $startedAt);
        $this->assertTrue($endedAt->isAfter($startedAt));
    }

    #[Test]
    public function it_can_create_a_recent_study_session(): void
    {
        $studySession = StudySession::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(StudySession::class, $studySession);
        $this->assertNotNull($studySession->started_at);

        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $studySession->started_at;
        $this->assertInstanceOf(CarbonInterface::class, $startedAt);
        // A recent study session should be started within the last 2 days
        $twoDaysAgo = now()->subDays(2);
        $this->assertTrue($startedAt->isAfter($twoDaysAgo));
    }

    #[Test]
    public function it_can_create_a_short_study_session(): void
    {
        $studySession = StudySession::factory()
            ->shortSession()
            ->create();

        $this->assertInstanceOf(StudySession::class, $studySession);
        $this->assertNotNull($studySession->started_at);
        $this->assertNotNull($studySession->ended_at);

        /** @var Carbon|CarbonImmutable $endedAt */
        $endedAt = $studySession->ended_at;
        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $studySession->started_at;
        $this->assertInstanceOf(CarbonInterface::class, $endedAt);
        $this->assertInstanceOf(CarbonInterface::class, $startedAt);
        // Calculate the duration in minutes
        $durationInMinutes = $startedAt->diffInMinutes($endedAt);

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

    #[Test]
    public function ended_at_is_after_started_at(): void
    {
        $studySession = StudySession::factory()->create();
        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $studySession->started_at;
        /** @var Carbon|CarbonImmutable $endedAt */
        $endedAt = $studySession->ended_at;
        $this->assertNotNull($startedAt);
        $this->assertNotNull($endedAt);
        $this->assertTrue($endedAt->isAfter($startedAt->toDateTimeString()));
    }

    #[Test]
    public function duration_is_calculated_correctly(): void
    {
        $started = now()->subMinutes(30);
        $ended = now();
        $session = StudySession::factory()->create([
            'started_at' => $started,
            'ended_at' => $ended,
        ]);

        $this->assertNotNull($session->started_at);
        $this->assertNotNull($session->ended_at);
        /** @var Carbon|CarbonImmutable $endedAt */
        $endedAt = $session->ended_at;
        /** @var Carbon|CarbonImmutable $startedAt */
        $startedAt = $session->started_at;
        $this->assertInstanceOf(CarbonInterface::class, $endedAt);
        $this->assertInstanceOf(CarbonInterface::class, $startedAt);

        // Cast to int to avoid floating point precision issues
        $duration = (int) $startedAt->diffInMinutes($endedAt);
        $this->assertEqualsWithDelta(30, $duration, 1);
    }
}
