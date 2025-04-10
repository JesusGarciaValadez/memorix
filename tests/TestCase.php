<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the base migrations first
        $this->artisan('migrate', ['--path' => 'database/migrations']);

        // Then run the module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);
    }
}
