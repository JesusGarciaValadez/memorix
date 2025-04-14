<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\seeders;

use App\Models\User;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\seeders\LogSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogSeederTest extends TestCase
{
    #[Test]
    public function it_seeds_logs_for_existing_users(): void
    {
        // Create users
        $users = User::factory()->count(2)->create();

        // Run the seeder
        $seeder = new LogSeeder();
        $seeder->run();

        // Check that logs were created
        $this->assertDatabaseCount('logs', 36); // 18 logs per user * 2 users

        // Check that logs belong to users
        foreach ($users as $user) {
            $userLogs = Log::where('user_id', $user->id)->get();
            $this->assertCount(18, $userLogs);
        }
    }

    #[Test]
    public function it_creates_users_if_none_exist_and_seeds_logs(): void
    {
        // Run the seeder
        $seeder = new LogSeeder();
        $seeder->run();

        // Check that users were created
        $this->assertDatabaseCount('users', 3);

        // Check that logs were created
        $this->assertDatabaseCount('logs', 54); // 18 logs per user * 3 users
    }

    #[Test]
    public function it_creates_different_types_of_logs(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Run the seeder
        $seeder = new LogSeeder();
        $seeder->run();

        // Get all logs for the user
        $userLogs = Log::where('user_id', $user->id)->get();

        // Check flashcard creation logs
        $creationLogs = $userLogs->filter(fn ($log): bool => $log->action === 'created_flashcard');
        $this->assertGreaterThanOrEqual(3, $creationLogs->count());

        // Check flashcard deletion logs
        $deletionLogs = $userLogs->filter(fn ($log): bool => $log->action === 'deleted_flashcard');
        $this->assertGreaterThanOrEqual(2, $deletionLogs->count());

        // Check study session start logs
        $startLogs = $userLogs->filter(fn ($log): bool => $log->action === 'started_study_session');
        $this->assertGreaterThanOrEqual(3, $startLogs->count());

        // Check study session end logs
        $endLogs = $userLogs->filter(fn ($log): bool => $log->action === 'ended_study_session');
        $this->assertGreaterThanOrEqual(2, $endLogs->count());
    }

    #[Test]
    public function it_includes_real_model_logs_when_models_exist(): void
    {
        // Create users, flashcards, and study sessions first
        $user = User::factory()->create();

        $flashcard = Flashcard::factory()
            ->for($user)
            ->create();

        $studySession = StudySession::factory()
            ->for($user)
            ->completed()
            ->create();

        // Run the seeder
        $seeder = new LogSeeder();
        $seeder->run();

        // Check for logs related to the specific models
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'created_flashcard',
            'details' => "Created flashcard ID: $flashcard->id, Question: $flashcard->question",
        ]);

        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'started_study_session',
            'details' => "Started study session ID: $studySession->id",
        ]);

        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'ended_study_session',
            'details' => "Ended study session ID: $studySession->id",
        ]);
    }
}
