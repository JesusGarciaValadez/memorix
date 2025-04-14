<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\database\seeders\FlashcardSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardSeederTest extends TestCase
{
    #[Test]
    public function it_creates_flashcards_for_existing_users(): void
    {
        // Create some users
        $usersCount = 2;
        User::factory()->count($usersCount)->create();

        // Run the seeder
        $seeder = new FlashcardSeeder();
        $seeder->run();

        // Calculate expected counts
        $expectedFlashcardsPerUser = 10 + 5 + 3; // standard + short + recent
        $expectedTotalFlashcards = $usersCount * $expectedFlashcardsPerUser;

        // Assert the correct number of flashcards were created
        $this->assertDatabaseCount('flashcards', $expectedTotalFlashcards);

        // Check each user has the expected flashcards
        foreach (User::all() as $user) {
            $userFlashcards = Flashcard::where('user_id', $user->id)->count();
            $this->assertEquals($expectedFlashcardsPerUser, $userFlashcards);
        }
    }

    #[Test]
    public function it_creates_users_if_none_exist(): void
    {
        // Ensure no users exist
        $this->assertDatabaseCount('users', 0);

        // Run the seeder
        $seeder = new FlashcardSeeder();
        $seeder->run();

        // Assert users were created
        $this->assertDatabaseCount('users', 3);

        // Assert flashcards were created for each user
        $expectedFlashcardsPerUser = 10 + 5 + 3; // standard + short + recent
        $expectedTotalFlashcards = 3 * $expectedFlashcardsPerUser;
        $this->assertDatabaseCount('flashcards', $expectedTotalFlashcards);
    }

    #[Test]
    public function it_creates_different_types_of_flashcards(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Run the seeder
        $seeder = new FlashcardSeeder();
        $seeder->run();

        // Get the user's flashcards
        $flashcards = Flashcard::where('user_id', $user->id)->get();

        // Check for recent flashcards (created within the last week)
        $recentFlashcards = $flashcards->filter(function ($flashcard) {
            return $flashcard->created_at->isAfter(now()->subWeek());
        });
        $this->assertGreaterThanOrEqual(3, $recentFlashcards->count());

        // We can't directly test for short answers since that's in the factory state
        // and not persisted in the database in any distinct way
        // But we can ensure the total count is correct
        $this->assertEquals(18, $flashcards->count());
    }
}
