<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;

final class PracticeResultSeeder extends Seeder
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

        // Create flashcards and study sessions if they don't exist
        if (Flashcard::count() === 0) {
            $this->call(FlashcardSeeder::class);
        }

        if (StudySession::count() === 0) {
            $this->call(StudySessionSeeder::class);
        }

        $users = User::all();

        // Create practice results for each user
        foreach ($users as $user) {
            // Get flashcards and study sessions for the user
            $flashcards = Flashcard::where('user_id', $user->id)->get();
            $studySession = StudySession::where('user_id', $user->id)
                ->whereNull('ended_at')
                ->first() ?? StudySession::factory()->create([
                    'user_id' => $user->id,
                    'started_at' => now()->subHours(1),
                    'ended_at' => null,
                ]);

            // For each flashcard, create practice results
            foreach ($flashcards as $flashcard) {
                // Create correct answers
                PracticeResult::factory()
                    ->count(rand(2, 5))
                    ->correct()
                    ->create([
                        'user_id' => $user->id,
                        'flashcard_id' => $flashcard->id,
                        'study_session_id' => $studySession->id,
                    ]);

                // Create incorrect answers
                PracticeResult::factory()
                    ->count(rand(0, 2))
                    ->incorrect()
                    ->create([
                        'user_id' => $user->id,
                        'flashcard_id' => $flashcard->id,
                        'study_session_id' => $studySession->id,
                    ]);

                // Create recent practice results
                PracticeResult::factory()
                    ->count(rand(1, 3))
                    ->recent()
                    ->create([
                        'user_id' => $user->id,
                        'flashcard_id' => $flashcard->id,
                        'study_session_id' => $studySession->id,
                    ]);
            }
        }
    }
}
