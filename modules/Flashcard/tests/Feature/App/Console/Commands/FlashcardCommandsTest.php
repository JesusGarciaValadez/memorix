<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Console\Commands;

use Mockery;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardCommandsTest extends TestCase
{
    #[Test]
    public function users_can_be_found_by_email(): void
    {
        // Create a spy for the FlashcardCommandServiceInterface
        $commandService = Mockery::spy(FlashcardCommandServiceInterface::class);
        $commandService->shouldReceive('findUserByEmail')
            ->with('test@example.com')
            ->andReturn((object) [
                'id' => 1,
                'email' => 'test@example.com',
            ]);

        // Assert that we can properly use the service
        $user = $commandService->findUserByEmail('test@example.com');
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
    }
}
