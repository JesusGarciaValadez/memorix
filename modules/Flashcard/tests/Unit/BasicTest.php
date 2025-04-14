<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;

final class BasicTest extends UnitTestCase
{
    #[Test]
    public function it_can_run_tests(): void
    {
        $this->assertTrue(true);
    }
}
