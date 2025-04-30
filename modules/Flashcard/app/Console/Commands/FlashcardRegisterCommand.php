<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class FlashcardRegisterCommand extends Command
{
    protected $signature = 'flashcard:register';

    protected $description = 'Register a new user for the flashcard application';

    /**
     * Execute the console command.
     */
    public function handle(FlashcardCommandServiceInterface $commandService): int
    {
        $commandService->registerUser($this);

        // Ask if the user wants to start the interactive mode
        if ($this->confirm('Do you want to use the flashcard application now?')) {
            $this->call('flashcard:interactive');
        }

        return CommandAlias::SUCCESS;
    }
}
