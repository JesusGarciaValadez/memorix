<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Policies;

use App\Models\User;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Policies\LogPolicy;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class LogPolicyTest extends TestCase
{
    private LogPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new LogPolicy();
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
        $log = Log::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->view($user, $log);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $log = Log::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->view($nonOwner, $log);

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
    public function it_updates_returns_false_regardless_of_ownership(): void
    {
        $user = User::factory()->create();
        $log = Log::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->update($user, $log);

        $this->assertFalse($result, 'Logs should not be updated after creation');
    }

    #[Test]
    public function it_deletes_returns_false_regardless_of_ownership(): void
    {
        $user = User::factory()->create();
        $log = Log::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->delete($user, $log);

        $this->assertFalse($result, 'Regular users should not be able to delete logs');
    }

    #[Test]
    public function it_views_activity_returns_true_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $result = $this->policy->viewActivity($user);

        $this->assertTrue($result);
    }
}
