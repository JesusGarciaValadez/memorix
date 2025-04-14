<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\seeders\PracticeResultSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PracticeResultSeederTest extends TestCase
{
    #[Test]
    public function it_creates_practice_results_for_existing_users(): void
    {
        // Create some users, flashcards, and a study session
        $user = User::factory()->create();
        $flashcards = Flashcard::factory()->count(3)->for($user)->create();
        $studySession = StudySession::factory()->for($user)->create([
            'ended_at' => null,
        ]);

        // Run the seeder
        $seeder = new PracticeResultSeeder();
        $seeder->run();

        // Assert practice results were created
        $practiceResults = PracticeResult::where('user_id', $user->id)->get();
        $this->assertNotEmpty($practiceResults);

        // Ensure each flashcard has practice results
        foreach ($flashcards as $flashcard) {
            $flashcardResults = $practiceResults->where('flashcard_id', $flashcard->id);
            $this->assertNotEmpty($flashcardResults);
        }
    }

    #[Test]
    public function it_creates_all_necessary_dependencies_if_they_dont_exist(): void
    {
        // Ensure no records exist
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('flashcards', 0);
        $this->assertDatabaseCount('study_sessions', 0);
        $this->assertDatabaseCount('practice_results', 0);

        // Run the seeder
        $seeder = new PracticeResultSeeder();
        $seeder->run();

        // Assert users, flashcards, study sessions, and practice results were created
        $this->assertNotEmpty(User::all());
        $this->assertNotEmpty(Flashcard::all());
        $this->assertNotEmpty(StudySession::all());
        $this->assertNotEmpty(PracticeResult::all());
    }

    #[Test]
    public function it_creates_different_types_of_practice_results(): void
    {
        // Create a user, flashcards, and a study session
        $user = User::factory()->create();
        Flashcard::factory()->count(3)->for($user)->create();
        StudySession::factory()->for($user)->create([
            'ended_at' => null,
        ]);

        // Run the seeder
        $seeder = new PracticeResultSeeder();
        $seeder->run();

        // Get practice results
        $practiceResults = PracticeResult::where('user_id', $user->id)->get();

        // Assert we have both correct and incorrect practice results
        $correctResults = $practiceResults->where('is_correct', true);
        $incorrectResults = $practiceResults->where('is_correct', false);

        $this->assertNotEmpty($correctResults);
        $this->assertNotEmpty($incorrectResults);

        // Assert we have recent practice results
        $recentResults = $practiceResults->filter(function ($result) {
            return $result->created_at->isAfter(now()->subWeek());
        });

        $this->assertNotEmpty($recentResults);
    }

    #[Test]
    public function it_creates_practice_results_linked_to_study_sessions(): void
    {
        // Create a user
        $user = User::factory()->create();
        $flashcards = Flashcard::factory()->count(3)->for($user)->create();
        $studySession = StudySession::factory()->for($user)->create([
            'ended_at' => null,
        ]);

        // Run the seeder
        $seeder = new PracticeResultSeeder();
        $seeder->run();

        // Get practice results and study sessions
        $practiceResults = PracticeResult::where('practice_results.user_id', $user->id)->get();
        $studySessions = StudySession::where('study_sessions.user_id', $user->id)->get();

        // Assert practice results are linked to study sessions
        foreach ($practiceResults as $result) {
            $this->assertNotNull($result->study_session_id);
            $this->assertTrue($studySessions->contains('id', $result->study_session_id));
        }
    }
}
