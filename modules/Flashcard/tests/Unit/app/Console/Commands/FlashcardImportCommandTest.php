<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Console\Commands;

use App\Models\User;
use Mockery;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardImportCommandTest extends TestCase
{
    private FlashcardCommandServiceInterface $commandService;

    private ConsoleRendererInterface $renderer;

    private User $user;

    private string $filePath = 'test-file.csv';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->commandService = Mockery::mock(FlashcardCommandServiceInterface::class);
        $this->app->instance(FlashcardCommandServiceInterface::class, $this->commandService);

        $this->renderer = $this->app->make(ConsoleRendererInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    #[Test]
    public function it_imports_flashcards_from_file_for_user(): void
    {
        // Create a temporary file for testing
        $tempFilePath = sys_get_temp_dir().'/'.$this->filePath;
        file_put_contents($tempFilePath, "question,answer\nTest Question,Test Answer");

        // Set up mock
        $this->commandService->shouldReceive('importFlashcardsFromFile')
            ->once()
            ->with($this->user->id, $tempFilePath)
            ->andReturn(true);

        $this->artisan('flashcard:import', [
            '--email' => $this->user->email,
            '--file' => $tempFilePath,
        ])
            ->assertSuccessful();

        // Clean up the temporary file
        @unlink($tempFilePath);
    }
}
