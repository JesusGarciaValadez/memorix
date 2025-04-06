<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

interface FlashcardActionInterface
{
    /**
     * Execute the flashcard action.
     */
    public function execute(): void;
}
