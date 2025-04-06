<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;

final readonly class ListFlashcardsAction implements FlashcardActionInterface
{
    public function __construct(private Command $command) {}

    public function execute(): void
    {
        $this->command->info('Listing all flashcards...');
        // Implementation will be added later
    }
}
