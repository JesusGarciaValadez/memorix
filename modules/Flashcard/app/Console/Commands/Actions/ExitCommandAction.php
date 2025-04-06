<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;

use function Termwind\render;

final class ExitCommandAction implements FlashcardActionInterface
{
    public function __construct(
        private readonly Command $command,
        private bool &$shouldKeepRunning
    ) {}

    public function execute(): void
    {
        render('<p class="p-3 bg-red-600 text-white font-bold">See you!</p>');
        $this->shouldKeepRunning = false;
    }
}
