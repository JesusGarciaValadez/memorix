<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Policies\LogPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogPolicyTest extends TestCase
{
    use RefreshDatabase;

    private LogPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new LogPolicy();
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
        $log = $this->createLog(1);

        $result = $this->policy->view($user, $log);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $user = $this->createUser(2);
        $log = $this->createLog(1);

        $result = $this->policy->view($user, $log);

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
    public function it_updates_returns_false_regardless_of_ownership(): void
    {
        $this->createUser(1);
        $this->createLog(1);

        $result = $this->policy->update();

        $this->assertFalse($result, 'Logs should not be updated after creation');
    }

    #[Test]
    public function it_deletes_returns_false_regardless_of_ownership(): void
    {
        $this->createUser(1);
        $this->createLog(1);

        $result = $this->policy->delete();

        $this->assertFalse($result, 'Regular users should not be able to delete logs');
    }

    #[Test]
    public function it_views_activity_returns_true_for_authenticated_user(): void
    {
        $this->createUser();

        $result = $this->policy->viewActivity();

        $this->assertTrue($result);
    }

    /**
     * Create a test User with the given ID
     */
    private function createUser(int $id = 1): User
    {
        return User::factory(['id' => $id])->make();
    }

    /**
     * Create a test Log with the given user_id
     */
    private function createLog(int $userId = 1): Log
    {
        return Log::factory(['user_id' => $userId])->make();
    }
}
