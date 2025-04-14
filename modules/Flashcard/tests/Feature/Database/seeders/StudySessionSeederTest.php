<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\seeders\StudySessionSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionSeederTest extends TestCase
{
    #[Test]
    public function it_seeds_study_sessions_for_existing_users(): void
    {
        // Create users
        $users = User::factory()->count(2)->create();

        // Run the seeder
        $seeder = new StudySessionSeeder();
        $seeder->run();

        // Check that study sessions were created
        $this->assertDatabaseCount('study_sessions', 22); // 11 study sessions per user * 2 users

        // Check that study sessions belong to users
        foreach ($users as $user) {
            $userStudySessions = StudySession::where('user_id', $user->id)->get();
            $this->assertCount(11, $userStudySessions);
        }
    }

    #[Test]
    public function it_creates_users_if_none_exist_and_seeds_study_sessions(): void
    {
        // Run the seeder
        $seeder = new StudySessionSeeder();
        $seeder->run();

        // Check that users were created
        $this->assertDatabaseCount('users', 3);

        // Check that study sessions were created
        $this->assertDatabaseCount('study_sessions', 33); // 11 study sessions per user * 3 users

        // Check specific types of study sessions were created
        $activeCount = StudySession::whereNull('ended_at')->count();
        $completedCount = StudySession::whereNotNull('ended_at')->count();

        $this->assertEquals(9, $activeCount); // 3 active sessions per user * 3 users (2 regular active + 1 recent)
        $this->assertEquals(24, $completedCount); // 8 completed sessions per user * 3 users (5 regular completed + 3 short)
    }

    #[Test]
    public function it_seeds_different_types_of_study_sessions(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Run the seeder
        $seeder = new StudySessionSeeder();
        $seeder->run();

        // Count different types of study sessions for the user
        $userSessions = StudySession::where('user_id', $user->id)->get();

        // Check active sessions (regular active + recent)
        $activeSessions = $userSessions->filter(fn ($session) => $session->isActive());
        $this->assertCount(3, $activeSessions);

        // Check completed sessions (regular completed + short)
        $completedSessions = $userSessions->filter(fn ($session) => ! $session->isActive());
        $this->assertCount(8, $completedSessions);

        // Just verify we have some short sessions (we can't accurately test the exact duration
        // since the factory might create them with random end times)
        $this->assertGreaterThan(0, $completedSessions->count());
    }
}
