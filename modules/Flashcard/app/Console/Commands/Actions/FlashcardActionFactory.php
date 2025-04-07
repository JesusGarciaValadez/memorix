<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StudySessionService;

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
            'list' => new ListFlashcardsAction(
                $command,
                app(FlashcardService::class),
                app(LogRepositoryInterface::class)
            ),
            'create' => new CreateFlashcardAction($command),
            'delete' => new DeleteFlashcardAction($command),
            'practice' => new PracticeFlashcardAction(
                $command,
                app(FlashcardRepositoryInterface::class),
                app(StudySessionRepositoryInterface::class),
                app(StatisticService::class),
                app(StudySessionService::class)
            ),
            'statistics' => new StatisticsFlashcardAction(
                $command,
                app(StatisticService::class)
            ),
            'reset' => new ResetFlashcardAction($command),
            'register' => new RegisterUserAction($command),
            'logs' => new ViewLogsAction(
                $command,
                $shouldKeepRunning,
                app(LogService::class)
            ),
            'exit' => new ExitCommandAction(
                $command,
                $shouldKeepRunning,
                app(LogRepositoryInterface::class)
            ),
            default => throw new InvalidArgumentException("Invalid action: {$action}"),
        };
    }
}
