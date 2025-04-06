<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;

final readonly class FlashcardService
{
    public function __construct(
        private FlashcardRepositoryInterface $flashcardRepository,
        private LogRepositoryInterface $logRepository,
        private StatisticRepositoryInterface $statisticRepository,
    ) {}

    /**
     * Get all flashcards for a user.
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->flashcardRepository->getAllForUser($userId, $perPage);
    }

    /**
     * Get all deleted flashcards for a user.
     */
    public function getDeletedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->flashcardRepository->getAllDeletedForUser($userId, $perPage);
    }

    /**
     * Create a new flashcard for a user.
     */
    public function create(int $userId, array $data): Flashcard
    {
        // Create the flashcard
        $data['user_id'] = $userId;
        $flashcard = $this->flashcardRepository->create($data);

        // Log the action
        $this->logRepository->logFlashcardCreation($userId, $flashcard);

        // Update statistics
        $this->statisticRepository->incrementFlashcardsCreated($userId);

        return $flashcard;
    }

    /**
     * Find a flashcard for a user.
     */
    public function findForUser(int $userId, int $flashcardId, bool $withTrashed = false): ?Flashcard
    {
        return $this->flashcardRepository->findForUser($flashcardId, $userId, $withTrashed);
    }

    /**
     * Update a flashcard for a user.
     */
    public function update(int $userId, int $flashcardId, array $data): bool
    {
        // Find the flashcard
        $flashcard = $this->flashcardRepository->findForUser($flashcardId, $userId);

        if (! $flashcard) {
            return false;
        }

        // Update the flashcard
        $result = $this->flashcardRepository->update($flashcard, $data);

        // Log the action
        if ($result) {
            $this->logRepository->logFlashcardUpdate($userId, $flashcard);
        }

        return $result;
    }

    /**
     * Delete a flashcard for a user.
     */
    public function delete(int $userId, int $flashcardId): bool
    {
        $flashcard = $this->flashcardRepository->findForUser($flashcardId, $userId);

        if (! $flashcard) {
            return false;
        }

        // Log the action before deletion
        $this->logRepository->logFlashcardDeletion($userId, $flashcard);

        // Delete the flashcard
        return $this->flashcardRepository->delete($flashcard);
    }

    /**
     * Restore a deleted flashcard.
     */
    public function restore(int $userId, int $flashcardId): bool
    {
        // Find the flashcard
        $flashcard = $this->flashcardRepository->findForUser($flashcardId, $userId, true);

        if (! $flashcard) {
            return false;
        }

        // Restore the flashcard
        $result = $this->flashcardRepository->restore($flashcard);

        // Log the action
        if ($result) {
            $this->logRepository->logFlashcardRestoration($userId, $flashcard);
        }

        return $result;
    }

    /**
     * Permanently delete a flashcard.
     */
    public function forceDelete(int $userId, int $flashcardId): bool
    {
        $flashcard = $this->flashcardRepository->findDeletedForUser($flashcardId, $userId);

        if (! $flashcard) {
            return false;
        }

        // Store flashcard data for logging
        $flashcardQuestion = $flashcard->question;

        // Force delete the flashcard
        $result = $this->flashcardRepository->forceDelete($flashcard);

        // Log the action after deletion
        if ($result) {
            $this->logRepository->logFlashcardForceDelete($userId, $flashcardId, $flashcardQuestion);
        }

        return $result;
    }
}
