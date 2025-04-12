<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Seeders;

use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\seeders\DatabaseSeeder;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DatabaseSeederTest extends TestCase
{
    #[Test]
    public function it_runs_all_seeders(): void
    {
        // Run the database seeder
        $seeder = new DatabaseSeeder();
        $seeder->run();

        // Verify that models from all seeders were created
        $this->assertGreaterThanOrEqual(3, \App\Models\User::count());

        // Check models from each seeder
        $this->assertDatabaseHas('flashcards', []);
        $this->assertGreaterThan(0, Flashcard::count());

        $this->assertDatabaseHas('study_sessions', []);
        $this->assertGreaterThan(0, StudySession::count());

        $this->assertDatabaseHas('statistics', []);
        $this->assertGreaterThan(0, Statistic::count());

        $this->assertDatabaseHas('logs', []);
        $this->assertGreaterThan(0, Log::count());
    }

    #[Test]
    public function it_creates_appropriate_relationships_between_models(): void
    {
        // Run the database seeder
        $seeder = new DatabaseSeeder();
        $seeder->run();

        // Get some models to test relationships
        $flashcard = Flashcard::first();
        $studySession = StudySession::first();
        $statistic = Statistic::first();
        $log = Log::first();

        // Verify user relationships
        $this->assertNotNull($flashcard->user);
        $this->assertNotNull($studySession->user);
        $this->assertNotNull($statistic->user);
        $this->assertNotNull($log->user);
    }
}
