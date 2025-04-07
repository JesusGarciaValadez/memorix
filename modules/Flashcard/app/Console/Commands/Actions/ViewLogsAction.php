<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Exception;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\LogServiceInterface;

final class ViewLogsAction implements FlashcardActionInterface
{
    public function __construct(
        private FlashcardInteractiveCommand $command,
        private bool &$shouldKeepRunning,
        private LogServiceInterface $logService,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        try {
            $logs = $this->logService->getLatestActivityForUser($this->command->user->id);

            if (empty($logs)) {
                $this->renderer->warning('No activity logs found');

                return;
            }

            foreach ($logs as $log) {
                $this->renderer->info(sprintf(
                    '[%s] %s - %s',
                    $log['level'],
                    $log['action'],
                    $log['details'] ?? ''
                ));
            }
        } catch (Exception $e) {
            $this->renderer->error('An error occurred while fetching logs: '.$e->getMessage());
        }
    }
}
