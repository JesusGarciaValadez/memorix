<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    // Override to prevent double migrations
    final public function refreshDatabase(): void
    {
        // Intentionally empty to prevent double migrations
    }
}
