<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Policies;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Policies\StatisticPolicy;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StatisticPolicyTest extends TestCase
{
    private StatisticPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new StatisticPolicy();
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
        $statistic = Statistic::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->view($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $statistic = Statistic::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->view($nonOwner, $statistic);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_false_for_any_user(): void
    {
        $user = User::factory()->create();

        $result = $this->policy->create($user);

        $this->assertFalse($result, 'Statistics are typically created automatically');
    }

    #[Test]
    public function it_updates_returns_false_for_any_user(): void
    {
        $user = User::factory()->create();
        $statistic = Statistic::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->update($user, $statistic);

        $this->assertFalse($result, 'Statistics should be updated through the increment methods');
    }

    #[Test]
    public function it_deletes_returns_false_for_any_user(): void
    {
        $user = User::factory()->create();
        $statistic = Statistic::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->delete($user, $statistic);

        $this->assertFalse($result, 'Statistics should generally not be deleted');
    }

    #[Test]
    public function it_resets_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $statistic = Statistic::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->reset($user, $statistic);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_resets_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $statistic = Statistic::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->reset($nonOwner, $statistic);

        $this->assertFalse($result);
    }
}
