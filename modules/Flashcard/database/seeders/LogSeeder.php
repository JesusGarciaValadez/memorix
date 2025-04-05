<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;

final class LogSeeder extends Seeder
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

        // Create general logs for each user
        foreach ($users as $user) {
            // Create random log entries
            Log::factory()
                ->count(5)
                ->for($user)
                ->create();

            // Create flashcard creation logs
            Log::factory()
                ->count(3)
                ->flashcardCreation()
                ->for($user)
                ->create();

            // Create flashcard deletion logs
            Log::factory()
                ->count(2)
                ->flashcardDeletion()
                ->for($user)
                ->create();

            // Create study session start logs
            Log::factory()
                ->count(3)
                ->studySessionStart()
                ->for($user)
                ->create();

            // Create study session end logs
            Log::factory()
                ->count(2)
                ->studySessionEnd()
                ->for($user)
                ->create();

            // Create recent logs
            Log::factory()
                ->count(3)
                ->recent()
                ->for($user)
                ->create();
        }

        // Create real logs based on actual models if they exist
        try {
            // Sample flashcard logs
            $flashcards = Flashcard::take(5)->get();
            foreach ($flashcards as $flashcard) {
                if ($flashcard && $flashcard->user) {
                    Log::logFlashcardCreation($flashcard->user, $flashcard);
                }
            }

            // Sample study session logs
            $studySessions = StudySession::take(5)->get();
            foreach ($studySessions as $session) {
                if ($session && $session->user) {
                    Log::logStudySessionStart($session->user, $session);

                    // Log session end for completed sessions
                    if (! $session->isActive()) {
                        Log::logStudySessionEnd($session->user, $session);
                    }
                }
            }
        } catch (Exception $e) {
            // Skip real model logging if there's an error
            // This ensures the seeder completes even if related models are not available
        }
    }
}
