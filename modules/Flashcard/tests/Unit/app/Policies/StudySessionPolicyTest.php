<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Policies;

use App\Models\User;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Policies\StudySessionPolicy;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StudySessionPolicyTest extends TestCase
{
    private StudySessionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new StudySessionPolicy();
    }

    #[Test]
    public function it_views_any_returns_true_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->view($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->view($nonOwner, $studySession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_true_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->update($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->update($nonOwner, $studySession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_returns_false_for_any_user(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->delete($user, $studySession);

        $this->assertFalse($result, 'Study sessions should generally not be deleted once created');
    }

    #[Test]
    public function it_ends_returns_true_for_owner_of_active_session(): void
    {
        $user = User::factory()->create();

        // Create an active study session (without ended_at)
        $activeSession = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        // We're testing both the ownership check and isActive() in the policy
        $this->assertTrue($activeSession->isActive(), 'Session should be active');

        $result = $this->policy->end($user, $activeSession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_ends_returns_false_for_owner_of_inactive_session(): void
    {
        $user = User::factory()->create();

        // Create an inactive study session (with ended_at)
        $inactiveSession = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);

        // We're testing both the ownership check and isActive() in the policy
        $this->assertFalse($inactiveSession->isActive(), 'Session should be inactive');

        $result = $this->policy->end($user, $inactiveSession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();

        // Even for an active session, non-owners can't end it
        $activeSession = StudySession::factory()->create([
            'user_id' => $owner->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $this->assertTrue($activeSession->isActive(), 'Session should be active');

        $result = $this->policy->end($nonOwner, $activeSession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_views_statistics_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->viewStatistics($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_statistics_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $studySession = StudySession::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->viewStatistics($nonOwner, $studySession);

        $this->assertFalse($result);
    }
}
