<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Models\Flashcard;

interface FlashcardRepositoryInterface
{
    /**
     * Get all flashcards for a user.
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all deleted flashcards for a user.
     */
    public function getAllDeletedForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a flashcard for a user.
     */
    public function findForUser(int $flashcardId, int $userId, bool $withTrashed = false): ?Flashcard;

    /**
     * Create a new flashcard.
     */
    public function create(array $data): Flashcard;

    /**
     * Update a flashcard.
     */
    public function update(Flashcard $flashcard, array $data): bool;

    /**
     * Delete a flashcard (soft delete).
     */
    public function delete(Flashcard $flashcard): bool;

    /**
     * Restore a deleted flashcard.
     */
    public function restore(Flashcard $flashcard): bool;

    /**
     * Permanently delete a flashcard.
     */
    public function forceDelete(Flashcard $flashcard): bool;

    /**
     * Restore all deleted flashcards for a user.
     */
    public function restoreAll(int $userId): bool;

    /**
     * Permanently delete all deleted flashcards for a user.
     */
    public function forceDeleteAll(int $userId): bool;
}
