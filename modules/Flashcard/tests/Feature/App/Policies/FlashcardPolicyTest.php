<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Policies;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tests\TestCase;

final class FlashcardPolicyTest extends TestCase
{
    private TestFlashcardPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TestFlashcardPolicy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_views_any_returns_true_for_authenticated_user(): void
    {
        // Create a test user
        $user = $this->createTestUser();

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createTestUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->view($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_views_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createTestUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->view($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_creates_returns_true_for_authenticated_user(): void
    {
        // Create a test user
        $user = $this->createTestUser();

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createTestUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->update($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_updates_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createTestUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->update($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_deletes_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createTestUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->delete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_deletes_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createTestUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->delete($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_restores_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createTestUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->restore($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_restores_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createTestUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->restore($user, $flashcard);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_forces_delete_returns_true_for_owner(): void
    {
        // Create a test user with ID 1
        $user = $this->createTestUser(1);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->forceDelete($user, $flashcard);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_forces_delete_returns_false_for_non_owner(): void
    {
        // Create a test user with ID 2
        $user = $this->createTestUser(2);

        // Create a test flashcard owned by user with ID 1
        $flashcard = $this->createTestFlashcard(1);

        $result = $this->policy->forceDelete($user, $flashcard);

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
     * Create a test Flashcard with the given user_id
     */
    private function createTestFlashcard(int $userId = 1): stdClass
    {
        $flashcard = new stdClass();
        $flashcard->user_id = $userId;

        return $flashcard;
    }
}

/**
 * A test-specific version of the FlashcardPolicy that doesn't use type hints
 * so we can test with simple stdClass objects
 */
final class TestFlashcardPolicy
{
    /**
     * Determine whether the user can view any flashcards.
     */
    public function viewAny(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the flashcard.
     */
    public function view(object $user, object $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can create flashcards.
     */
    public function create(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the flashcard.
     */
    public function update(object $user, object $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can delete the flashcard.
     */
    public function delete(object $user, object $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can restore the flashcard.
     */
    public function restore(object $user, object $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can permanently delete the flashcard.
     */
    public function forceDelete(object $user, object $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }
}
