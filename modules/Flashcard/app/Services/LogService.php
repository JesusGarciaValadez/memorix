<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final readonly class LogService implements LogServiceInterface
{
    public function __construct(
        private LogRepositoryInterface $logRepository,
    ) {}

    /**
     * Get logs for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForUser(int $userId, int $limit = 50): array
    {
        return $this->logRepository->getLogsForUser($userId, $limit);
    }

    /**
     * Get latest activity for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestActivityForUser(int $userId, int $limit = 10): array
    {
        return $this->logRepository->getLogsForUser($userId, $limit);
    }
}
