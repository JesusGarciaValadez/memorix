<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests;

use Modules\Flashcard\app\Providers\FlashcardServiceProvider;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FlashcardServiceProvider::class,
        ];
    }
}
