<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final class ExitCommandAction implements FlashcardActionInterface
{
    public function __construct(
        private readonly Command $command,
        private bool &$shouldKeepRunning,
        private readonly LogRepositoryInterface $logRepository,
        private readonly ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->getUser();
        }

        if ($user) {
            $this->logRepository->logUserExit($user->id);
        }

        $this->renderer->error('See you!');
        $this->shouldKeepRunning = false;
    }
}
