<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Flashcard\app\Models\Statistic;

final class StatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some users if none exist
        if (User::count() === 0) {
            User::factory()->count(3)->create();
        }

        $users = User::all();

        // Create statistics for each user
        foreach ($users as $user) {
            // Create statistic with default values for regular users
            if ($user->id) {
                Statistic::factory()
                    ->for($user, 'user')
                    ->create();
            }
        }

        // Create a user with high success rate
        $highSuccessUser = User::first() ?? User::factory()->create();

        if ($highSuccessUser->id) {
            Statistic::factory()
                ->highSuccess()
                ->for($highSuccessUser, 'user')
                ->create();
        }

        // Create a user with low success rate
        $lowSuccessUser = User::skip(1)->first() ?? User::factory()->create();

        if ($lowSuccessUser->id) {
            Statistic::factory()
                ->lowSuccess()
                ->for($lowSuccessUser, 'user')
                ->create();
        }

        // Create a power user
        $powerUser = User::factory()->create();

        if ($powerUser->id) {
            Statistic::factory()
                ->powerUser()
                ->for($powerUser, 'user')
                ->create();
        }

        // Create a new user with minimal stats
        $newUser = User::factory()->create();

        if ($newUser->id) {
            Statistic::factory()
                ->newUser()
                ->for($newUser, 'user')
                ->create();
        }

        $user1 = User::where('email', 'admin@example.com')->first();
        $user2 = User::where('email', 'test@example.com')->first();

        if ($user1 && $user1->id) {
            Statistic::factory()
                ->for($user1, 'user')
                ->create([
                    'total_flashcards' => 10,
                    'total_study_sessions' => 5,
                ]);
        }

        if ($user2 && $user2->id) {
            Statistic::factory()
                ->for($user2, 'user')
                ->create([
                    'total_flashcards' => 5,
                    'total_study_sessions' => 2,
                ]);
        }
    }
}
