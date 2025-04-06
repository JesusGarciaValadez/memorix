<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;

final readonly class CreateFlashcardAction implements FlashcardActionInterface
{
    public function __construct(private Command $command) {}

    public function execute(): void
    {
        $this->command->info('Creating a new flashcard...');
        // Implementation will be added later
    }
}
