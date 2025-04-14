<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit;

use Modules\Flashcard\Tests\TestCase;

/**
 * Base TestCase for Flashcard Unit Tests
 *
 * Unit tests are focused on testing individual components in isolation.
 * They typically:
 * - Test a single class or method
 * - Mock external dependencies
 * - Don't interact with the database, filesystem, or network
 * - Are fast and focused
 * - Test internal logic and behavior
 *
 * Extend this class for tests that focus on testing small, isolated
 * components without external dependencies.
 */
abstract class UnitTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Setup for unit tests
        // No database setup is performed for unit tests by default
        // Dependencies should be mocked instead
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        // Any unit test cleanup needed

        parent::tearDown();
    }
}
