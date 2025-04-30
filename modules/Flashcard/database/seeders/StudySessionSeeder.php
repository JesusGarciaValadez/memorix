<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Flashcard\app\Models\StudySession;

final class StudySessionSeeder extends Seeder
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

        // Create study sessions for each user
        foreach ($users as $user) {
            // Create active study sessions
            StudySession::factory()
                ->count(2)
                ->active()
                ->for($user)
                ->create();

            // Create completed study sessions
            StudySession::factory()
                ->count(5)
                ->completed()
                ->for($user)
                ->create();

            // Create recent study sessions
            StudySession::factory()
                ->count(1)
                ->recent()
                ->active()
                ->for($user)
                ->create();

            // Create short study sessions
            StudySession::factory()
                ->count(3)
                ->shortSession()
                ->completed()
                ->for($user)
                ->create();
        }
    }
}
