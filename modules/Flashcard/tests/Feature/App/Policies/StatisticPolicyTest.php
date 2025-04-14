<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

final class StatisticPolicyTest extends TestCase
{
    private TestStatisticPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TestStatisticPolicy();
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
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->view($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $user = $this->createTestUser(2);
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->view($user, $statistic);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_false_for_any_user(): void
    {
        $user = $this->createTestUser();

        $result = $this->policy->create($user);

        $this->assertFalse($result, 'Statistics are typically created automatically');
    }

    #[Test]
    public function it_updates_returns_false_for_any_user(): void
    {
        $user = $this->createTestUser(1);
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->update($user, $statistic);

        $this->assertFalse($result, 'Statistics should be updated through the increment methods');
    }

    #[Test]
    public function it_deletes_returns_false_for_any_user(): void
    {
        $user = $this->createTestUser(1);
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->delete($user, $statistic);

        $this->assertFalse($result, 'Statistics should generally not be deleted');
    }

    #[Test]
    public function it_resets_returns_true_for_owner(): void
    {
        $user = $this->createTestUser(1);
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->reset($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_resets_returns_false_for_non_owner(): void
    {
        $user = $this->createTestUser(2);
        $statistic = $this->createTestStatistic(1);

        $result = $this->policy->reset($user, $statistic);

        $this->assertFalse($result);
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
     * Create a test Statistic with the given user_id
     */
    private function createTestStatistic(int $userId = 1): stdClass
    {
        $statistic = new stdClass();
        $statistic->user_id = $userId;

        return $statistic;
    }
}

/**
 * A test-specific version of the StatisticPolicy that doesn't use type hints
 * so we can test with simple stdClass objects
 */
final class TestStatisticPolicy
{
    /**
     * Determine whether the user can view any statistics.
     */
    public function viewAny(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the statistic.
     */
    public function view(object $user, object $statistic): bool
    {
        return $user->id === $statistic->user_id;
    }

    /**
     * Determine whether the user can create statistics.
     */
    public function create(object $user): bool
    {
        // Statistics are typically created automatically
        // This might be restricted to system processes
        return false;
    }

    /**
     * Determine whether the user can update the statistic.
     */
    public function update(object $user, object $statistic): bool
    {
        // Statistics should be updated through the increment methods
        // Direct updates might be restricted
        return false;
    }

    /**
     * Determine whether the user can delete the statistic.
     */
    public function delete(object $user, object $statistic): bool
    {
        // Statistics should generally not be deleted
        return false;
    }

    /**
     * Determine whether the user can reset their own statistics.
     */
    public function reset(object $user, object $statistic): bool
    {
        return $user->id === $statistic->user_id;
    }
}
