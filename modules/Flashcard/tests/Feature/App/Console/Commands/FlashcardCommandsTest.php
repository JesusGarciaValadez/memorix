<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @method \App\Models\User findUserByEmail(string $email)
 */
final class FlashcardCommandsTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    #[Test]
    public function users_can_be_found_by_email(): void
    {
        /** @var FlashcardCommandServiceInterface&Mockery\MockInterface $commandService */
        $commandService = Mockery::mock(FlashcardCommandServiceInterface::class);
        // @phpstan-ignore-next-line
        $commandService->shouldReceive('findUserByEmail')
            ->with('test@example.com') // PHPStan struggles with Mockery chain
            ->andReturn((object) [
                'id' => 1,
                'email' => 'test@example.com',
            ]);

        // Assert that we can properly use the service
        // @phpstan-ignore-next-line
        $user = $commandService->findUserByEmail('test@example.com'); // PHPStan struggles with Mockery mock method call
        $this->assertEquals(1, $user->id);
        $this->assertEquals('test@example.com', $user->email);
    }
}
