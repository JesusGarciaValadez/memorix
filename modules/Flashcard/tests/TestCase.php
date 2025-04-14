<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base TestCase for all Flashcard Module tests
 *
 * This class provides common functionality for both unit and feature tests
 * for the Flashcard module. It extends Laravel's base TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Common setup for all tests in the Flashcard module
        // ...
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        // Common teardown for all tests in the Flashcard module
        // ...

        parent::tearDown();
    }

    /**
     * Sets up core database migrations for testing.
     *
     * This method runs the core Laravel migrations and can be called
     * from subclasses as needed.
     */
    final public function setupCoreDatabase(): void
    {
        // Run core Laravel migrations
        $this->artisan('migrate', ['--path' => 'database/migrations']);
    }
}
