<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use InvalidArgumentException;

final class FlashcardActionFactory
{
    /**
     * Create a Flashcard action.
     */
    public static function create(
        string $action,
        Command $command,
        ?bool &$shouldKeepRunning = null
    ): FlashcardActionInterface {
        return match ($action) {
            'list' => new ListFlashcardsAction($command),
            'create' => new CreateFlashcardAction($command),
            'delete' => new DeleteFlashcardAction($command),
            'practice' => new PracticeFlashcardAction($command),
            'statistics' => new StatisticsFlashcardAction($command),
            'reset' => new ResetFlashcardAction($command),
            'register' => new RegisterUserAction($command),
            'exit' => new ExitCommandAction($command, $shouldKeepRunning),
            default => throw new InvalidArgumentException("Invalid action: {$action}"),
        };
    }
}
