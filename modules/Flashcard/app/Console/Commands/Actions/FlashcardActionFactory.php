<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
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
                app(LogRepositoryInterface::class),
                app(ConsoleRendererInterface::class)
            ),
            'create' => new CreateFlashcardAction(
                $command,
                app(ConsoleRendererInterface::class)
            ),
            'delete' => new DeleteFlashcardAction(
                $command,
                app(ConsoleRendererInterface::class)
            ),
            'practice' => new PracticeFlashcardAction(
                $command,
                app(StudySessionRepositoryInterface::class),
                app(StatisticService::class),
                app(StudySessionService::class),
                app(ConsoleRendererInterface::class)
            ),
            'statistics' => new StatisticsFlashcardAction(
                $command,
                app(StatisticRepositoryInterface::class),
                app(ConsoleRendererInterface::class)
            ),
            'reset' => new ResetFlashcardAction(
                $command,
                app(StatisticRepositoryInterface::class),
                app(PracticeResultRepositoryInterface::class),
                app(StudySessionRepositoryInterface::class),
                app(LogRepositoryInterface::class),
                app(ConsoleRendererInterface::class)
            ),
            'register' => new RegisterUserAction($command),
            'logs' => new ViewLogsAction(
                $command,
                $shouldKeepRunning,
                app(LogService::class),
                app(ConsoleRendererInterface::class)
            ),
            'trash-bin' => new TrashBinAction(
                $command,
                app(LogRepositoryInterface::class),
                app(ConsoleRendererInterface::class)
            ),
            'exit' => new ExitCommandAction(
                $command,
                $shouldKeepRunning,
                app(LogRepositoryInterface::class),
                app(ConsoleRendererInterface::class)
            ),
            default => throw new InvalidArgumentException("Invalid action: {$action}"),
        };
    }
}
