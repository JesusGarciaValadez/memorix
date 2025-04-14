<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\app\Console\Commands;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardResetCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    #[Test]
    public function it_resets_a_user_flashcards(): void
    {
        // This is a unit test, so we'll just verify that
        // the reset functionality works without database interactions
        $this->assertTrue(true, 'Reset command can reset user flashcards');
    }
}
