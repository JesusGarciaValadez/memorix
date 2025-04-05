<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Flashcard\app\Models\Flashcard;

final class FlashcardSeeder extends Seeder
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

        // Create flashcards for each user
        foreach ($users as $user) {
            // Create standard flashcards
            Flashcard::factory()
                ->count(10)
                ->for($user)
                ->create();

            // Create flashcards with short answers
            Flashcard::factory()
                ->count(5)
                ->shortAnswer()
                ->for($user)
                ->create();

            // Create recent flashcards
            Flashcard::factory()
                ->count(3)
                ->recent()
                ->for($user)
                ->create();
        }
    }
}
