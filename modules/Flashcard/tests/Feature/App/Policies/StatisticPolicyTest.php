<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Policies\StatisticPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticPolicyTest extends TestCase
{
    use RefreshDatabase;

    private StatisticPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new StatisticPolicy();
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
        $statistic = $this->createStatistic(1);

        $result = $this->policy->view($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $statistic = $this->createStatistic(1);

        $result = $this->policy->view($user, $statistic);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_false_for_any_user(): void
    {
        $this->createUser();

        $result = $this->policy->create();

        $this->assertFalse($result, 'Statistics are typically created automatically');
    }

    #[Test]
    public function it_updates_returns_false_for_any_user(): void
    {
        $this->createUser(1);
        $this->createStatistic(1);

        $result = $this->policy->update();

        $this->assertFalse($result, 'Statistics should be updated through the increment methods');
    }

    #[Test]
    public function it_deletes_returns_false_for_any_user(): void
    {
        $this->createUser(1);
        $this->createStatistic(1);

        $result = $this->policy->delete();

        $this->assertFalse($result, 'Statistics should generally not be deleted');
    }

    #[Test]
    public function it_resets_returns_true_for_owner(): void
    {
        $user = $this->createUser(1);
        $statistic = $this->createStatistic(1);

        $result = $this->policy->reset($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_resets_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $statistic = $this->createStatistic(1);

        $result = $this->policy->reset($user, $statistic);

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
     * Create a test Statistic with the given user_id
     */
    private function createStatistic(int $userId = 1): Statistic
    {
        return Statistic::factory(['user_id' => $userId])->make();
    }
}
