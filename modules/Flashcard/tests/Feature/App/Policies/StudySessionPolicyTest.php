<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Policies\StudySessionPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private StudySessionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new StudySessionPolicy();
    }

    #[Test]
    public function it_views_any_returns_true_for_authenticated_user(): void
    {
        $this->createUser();

        $result = $this->policy->viewAny();

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_true_for_owner(): void
    {
        $user = $this->createUser(1);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->view($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->view($user, $studySession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_true_for_authenticated_user(): void
    {
        $this->createUser();

        $result = $this->policy->create();

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_true_for_owner(): void
    {
        $user = $this->createUser(1);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->update($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->update($user, $studySession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_returns_false_for_any_user(): void
    {
        $this->createUser(1);
        $this->createStudySession(1);

        $result = $this->policy->delete();

        $this->assertFalse($result, 'Study sessions should generally not be deleted once created');
    }

    #[Test]
    public function it_ends_returns_true_for_owner_of_active_session(): void
    {
        $user = $this->createUser(1);
        $activeSession = $this->createStudySession(1, true);

        // We're testing both the ownership check and isActive() in the policy
        $this->assertTrue($activeSession->isActive(), 'Session should be active');

        $result = $this->policy->end($user, $activeSession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_ends_returns_false_for_owner_of_inactive_session(): void
    {
        $user = $this->createUser(1);
        $inactiveSession = $this->createStudySession(1, false);

        // We're testing both the ownership check and isActive() in the policy
        $this->assertFalse($inactiveSession->isActive(), 'Session should be inactive');

        $result = $this->policy->end($user, $inactiveSession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_ends_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $activeSession = $this->createStudySession(1, true);

        $this->assertTrue($activeSession->isActive(), 'Session should be active');

        $result = $this->policy->end($user, $activeSession);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_views_statistics_returns_true_for_owner(): void
    {
        $user = $this->createUser(1);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->viewStatistics($user, $studySession);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_statistics_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $studySession = $this->createStudySession(1);

        $result = $this->policy->viewStatistics($user, $studySession);

        $this->assertFalse($result);
    }

    /**
     * Create a test User with the given ID
     */
    private function createUser(int $id = 1): User
    {
        return User::factory(['id' => $id])->make();
    }

    /**
     * Create a test StudySession with the given user_id and active status
     */
    private function createStudySession(int $userId = 1, bool $isActive = true): StudySession
    {
        if ($isActive) {
            return StudySession::factory([
                'user_id' => $userId,
                'is_active' => null,
            ])->make();
        }

        return StudySession::factory([
            'user_id' => $userId,
            'ended_at' => now()->subHours(2),
        ])->make();
    }
}
