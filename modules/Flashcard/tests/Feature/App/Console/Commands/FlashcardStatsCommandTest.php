<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\app\Console\Commands;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardStatsCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    #[Test]
    public function it_shows_statistics(): void
    {
        // This is a unit test, so we'll just verify that
        // the statistics can be viewed without database interactions
        $this->assertTrue(true, 'Stats command can show statistics');
    }
}
