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
            Statistic::factory()
                ->for($user)
                ->create();
        }

        // Create a user with high success rate
        if (User::count() >= 4) {
            $highSuccessUser = User::first();

            Statistic::factory()
                ->highSuccess()
                ->for($highSuccessUser)
                ->create();
        } else {
            $highSuccessUser = User::factory()->create();

            Statistic::factory()
                ->highSuccess()
                ->for($highSuccessUser)
                ->create();
        }

        // Create a user with low success rate
        if (User::count() >= 5) {
            $lowSuccessUser = User::skip(1)->first();

            Statistic::factory()
                ->lowSuccess()
                ->for($lowSuccessUser)
                ->create();
        } else {
            $lowSuccessUser = User::factory()->create();

            Statistic::factory()
                ->lowSuccess()
                ->for($lowSuccessUser)
                ->create();
        }

        // Create a power user
        $powerUser = User::factory()->create();

        Statistic::factory()
            ->powerUser()
            ->for($powerUser)
            ->create();

        // Create a new user with minimal stats
        $newUser = User::factory()->create();

        Statistic::factory()
            ->newUser()
            ->for($newUser)
            ->create();
    }
}
