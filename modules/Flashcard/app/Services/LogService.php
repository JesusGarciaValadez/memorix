<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final readonly class LogService
{
    public function __construct(
        private LogRepositoryInterface $logRepository,
    ) {}

    /**
     * Get logs for a user.
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return $this->logRepository->getLogsForUser($userId, $limit);
    }

    /**
     * Get latest activity for a user.
     */
    public function getLatestActivityForUser(int $userId, int $limit = 10): array
    {
        $logs = $this->logRepository->getLogsForUser($userId, $limit);

        $activities = [];
        foreach ($logs as $log) {
            $activities[] = [
                'id' => $log->id,
                'action' => $log->action,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'details' => $log->details,
            ];
        }

        return $activities;
    }
}
