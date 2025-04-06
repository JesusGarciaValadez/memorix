<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;

final readonly class DeleteFlashcardAction implements FlashcardActionInterface
{
    public function __construct(private Command $command) {}

    public function execute(): void
    {
        $this->command->info('Deleting a flashcard...');
        // Implementation will be added later
    }
}
