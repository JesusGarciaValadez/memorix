<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\database\seeders\StatisticSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticSeederTest extends TestCase
{
    #[Test]
    public function it_seeds_statistics_for_existing_users(): void
    {
        // Create users
        $users = User::factory()->count(3)->create();

        // Run the seeder
        $seeder = new StatisticSeeder();
        $seeder->run();

        // Check that statistics were created for existing users
        $this->assertDatabaseCount('statistics', 7); // 3 existing users + 4 special users

        // Check that statistics belong to users
        foreach ($users as $user) {
            $this->assertDatabaseHas('statistics', ['user_id' => $user->id]);
        }
    }

    #[Test]
    public function it_creates_users_if_none_exist_and_seeds_statistics(): void
    {
        // Run the seeder
        $seeder = new StatisticSeeder();
        $seeder->run();

        // Check that users were created
        $this->assertGreaterThanOrEqual(7, User::count()); // At least 3 default + 4 special users

        // Check that statistics were created
        $this->assertDatabaseCount('statistics', 7); // 3 existing users + 4 special users
    }

    #[Test]
    public function it_creates_statistics_with_different_profiles(): void
    {
        // Run the seeder
        $seeder = new StatisticSeeder();
        $seeder->run();

        // Get all statistics
        $statistics = Statistic::all();

        // Test high success user
        $highSuccessStats = $statistics->first(fn ($stat): bool => $stat->getCorrectPercentage() >= 80);
        $this->assertNotNull($highSuccessStats);

        // Test low success user
        $lowSuccessStats = $statistics->first(fn ($stat): bool => $stat->getCorrectPercentage() <= 40 && $stat->getCorrectPercentage() > 0);
        $this->assertNotNull($lowSuccessStats);

        // Test power user
        $powerUserStats = $statistics->first(fn ($stat): bool => $stat->total_flashcards >= 200);
        $this->assertNotNull($powerUserStats);

        // Test new user
        $newUserStats = $statistics->first(fn ($stat): bool => $stat->total_flashcards <= 5);
        $this->assertNotNull($newUserStats);
    }
}
