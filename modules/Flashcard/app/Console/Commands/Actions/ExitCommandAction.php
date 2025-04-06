<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;

final class ExitCommandAction implements FlashcardActionInterface
{
    public function __construct(
        private readonly Command $command,
        private bool &$shouldKeepRunning
    ) {}

    public function execute(): void
    {
        ConsoleRenderer::error('See you!');
        $this->shouldKeepRunning = false;
    }
}
