<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;

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
            'list' => new ListFlashcardsAction($command, app(FlashcardService::class)),
            'create' => new CreateFlashcardAction($command),
            'delete' => new DeleteFlashcardAction($command),
            'practice' => new PracticeFlashcardAction(
                $command,
                app(FlashcardRepositoryInterface::class),
                app(StudySessionRepositoryInterface::class)
            ),
            'statistics' => new StatisticsFlashcardAction($command),
            'reset' => new ResetFlashcardAction($command),
            'register' => new RegisterUserAction($command),
            'exit' => new ExitCommandAction($command, $shouldKeepRunning),
            default => throw new InvalidArgumentException("Invalid action: {$action}"),
        };
    }
}
