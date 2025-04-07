<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

interface LogServiceInterface
{
    /**
     * Get logs for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogsForUser(int $userId, int $limit = 50): array;

    /**
     * Get latest activity for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestActivityForUser(int $userId, int $limit = 10): array;
}
