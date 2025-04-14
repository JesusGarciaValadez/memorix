<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Providers\FlashcardServiceProvider;
use Modules\Flashcard\Tests\TestCase;

/**
 * Base TestCase for Flashcard Feature Tests
 *
 * Feature tests are higher-level tests that test larger system components
 * or end-to-end functionality. They typically:
 * - Interact with the full application stack
 * - Use actual database interactions (not mocks)
 * - May test API endpoints, routes, middleware
 * - Test how components work together
 * - May be slower than unit tests
 *
 * Extend this class for any tests that require database integration,
 * multiple component interaction, or HTTP requests.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // For SQLite in-memory database, we need to turn off foreign key checks
        // during migrations to prevent issues with circular references
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        // Setup the core database
        $this->setupCoreDatabase();

        // Run the module migrations for the Flashcard module
        $this->artisan('migrate', [
            '--path' => 'modules/Flashcard/database/migrations',
        ]);

        // Register all necessary service providers
        $this->app->register(FlashcardServiceProvider::class);

        // Seed required data for feature tests
        $this->seed(\Modules\Flashcard\database\seeders\DatabaseSeeder::class);

        // Re-enable foreign key checks if using SQLite
        if (config('database.default') === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        // Any feature test cleanup needed

        parent::tearDown();
    }
}
