<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

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
        // Create a test user
        $this->createUser();

        $result = $this->policy->viewAny();

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->view($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->view($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_true_for_authenticated_user(): void
    {
        // Create a test user
        $this->createUser();

        $result = $this->policy->create();

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->update($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->update($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->delete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_deletes_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->delete($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->restore($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_restores_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->restore($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_forces_delete_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->forceDelete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_forces_delete_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createFlashcard(1);

        $result = $this->policy->forceDelete($user, $flashcard);

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
     * Create a test Flashcard with the given user_id
     */
    private function createFlashcard(int $userId = 1): Flashcard
    {
        return Flashcard::factory(['user_id' => $userId])->make();
    }
}
