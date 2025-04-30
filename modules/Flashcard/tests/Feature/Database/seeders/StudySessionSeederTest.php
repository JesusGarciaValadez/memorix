<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\seeders\StudySessionSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @noinspection StaticInvocationViaThisInspection */
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
        $users->each(function (User $user): void {
            $userStudySessions = StudySession::where('user_id', $user->id)->get();
            $this->assertCount(11, $userStudySessions);
        });
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
        // Run the seeder
        $seeder = new StudySessionSeeder();
        $seeder->run();

        // Since we're using the factory to create study sessions, we can check the total counts instead
        $totalSessions = StudySession::count();
        $this->assertEquals(33, $totalSessions); // 11 sessions * 3 users

        // Check active sessions (regular active + recent)
        $activeSessions = StudySession::whereNull('ended_at')->get();
        $this->assertCount(9, $activeSessions); // 3 active per user * 3 users

        // Check completed sessions (regular completed + short)
        $completedSessions = StudySession::whereNotNull('ended_at')->get();
        $this->assertCount(24, $completedSessions); // 8 completed per user * 3 users
    }
}
