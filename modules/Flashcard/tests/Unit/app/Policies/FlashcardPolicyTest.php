<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Policies\FlashcardPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardPolicyTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new FlashcardPolicy();
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
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->view($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->view($nonOwner, $flashcard);

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
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->update($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->update($nonOwner, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->delete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_deletes_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->delete($nonOwner, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->restore($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_restores_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->restore($nonOwner, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_forces_delete_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->forceDelete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_forces_delete_returns_false_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $owner->id]);

        $result = $this->policy->forceDelete($nonOwner, $flashcard);

        $this->assertFalse($result);
    }
}
