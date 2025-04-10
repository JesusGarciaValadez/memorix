<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase as BaseTestCase;

final class FlashcardTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }
    }

    protected function tearDown(): void
    {
        // Re-enable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }

        parent::tearDown();
    }

    protected function defineDatabaseMigrations(): void
    {
        // First run Laravel's base migrations
        $this->loadLaravelMigrations(['--path' => 'database/migrations']);

        // Then run Flashcard module migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
