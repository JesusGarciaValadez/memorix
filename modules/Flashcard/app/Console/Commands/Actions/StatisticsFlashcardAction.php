<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;

final readonly class StatisticsFlashcardAction implements FlashcardActionInterface
{
    public function __construct(private Command $command) {}

    public function execute(): void
    {
        $this->command->info('Showing statistics...');
        // Implementation will be added later
    }
}
