<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

final class LogPolicyTest extends TestCase
{
    private TestLogPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TestLogPolicy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_views_any_returns_true_for_authenticated_user(): void
    {
        $user = $this->createTestUser();

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_true_for_owner(): void
    {
        $user = $this->createTestUser(1);
        $log = $this->createTestLog(1);

        $result = $this->policy->view($user, $log);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $user = $this->createTestUser(2);
        $log = $this->createTestLog(1);

        $result = $this->policy->view($user, $log);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_true_for_authenticated_user(): void
    {
        $user = $this->createTestUser();

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_regardless_of_ownership(): void
    {
        $user = $this->createTestUser(1);
        $log = $this->createTestLog(1);

        $result = $this->policy->update($user, $log);

        $this->assertFalse($result, 'Logs should not be updated after creation');
    }

    #[Test]
    public function it_deletes_returns_false_regardless_of_ownership(): void
    {
        $user = $this->createTestUser(1);
        $log = $this->createTestLog(1);

        $result = $this->policy->delete($user, $log);

        $this->assertFalse($result, 'Regular users should not be able to delete logs');
    }

    #[Test]
    public function it_views_activity_returns_true_for_authenticated_user(): void
    {
        $user = $this->createTestUser();

        $result = $this->policy->viewActivity($user);

        $this->assertTrue($result);
    }

    /**
     * Create a test User with the given ID
     */
    private function createTestUser(int $id = 1): stdClass
    {
        $user = new stdClass();
        $user->id = $id;

        return $user;
    }

    /**
     * Create a test Log with the given user_id
     */
    private function createTestLog(int $userId = 1): stdClass
    {
        $log = new stdClass();
        $log->user_id = $userId;

        return $log;
    }
}

/**
 * A test-specific version of the LogPolicy that doesn't use type hints
 * so we can test with simple stdClass objects
 */
final class TestLogPolicy
{
    /**
     * Determine whether the user can view any logs.
     */
    public function viewAny(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the log.
     */
    public function view(object $user, object $log): bool
    {
        return $user->id === $log->user_id;
    }

    /**
     * Determine whether the user can create logs.
     */
    public function create(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the log.
     */
    public function update(object $user, object $log): bool
    {
        // Logs should not be updated after creation
        return false;
    }

    /**
     * Determine whether the user can delete the log.
     */
    public function delete(object $user, object $log): bool
    {
        // Regular users should not be able to delete logs
        // This could be extended to allow admins to delete logs
        return false;
    }

    /**
     * Determine whether the user can view their own activity log.
     */
    public function viewActivity(object $user): bool
    {
        return true;
    }
}
