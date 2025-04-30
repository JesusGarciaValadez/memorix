<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Models\Flashcard;

interface FlashcardServiceInterface
{
    /**
     * Get all flashcards for a user.
     *
     * @return LengthAwarePaginator<int, Flashcard>
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all deleted flashcards for a user.
     *
     * @return LengthAwarePaginator<int, Flashcard>
     */
    public function getDeletedForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new flashcard for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $userId, array $data): Flashcard;

    /**
     * Find a flashcard for a user.
     */
    public function findForUser(int $userId, int $flashcardId, bool $withTrashed = false): ?Flashcard;

    /**
     * Update a flashcard for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $userId, int $flashcardId, array $data): bool;

    /**
     * Delete a flashcard for a user.
     */
    public function delete(int $userId, int $flashcardId): bool;

    /**
     * Restore a deleted flashcard.
     */
    public function restore(int $userId, int $flashcardId): bool;

    /**
     * Permanently delete a flashcard.
     */
    public function forceDelete(int $userId, int $flashcardId): bool;

    /**
     * Restore all deleted flashcards for a user.
     */
    public function restoreAllForUser(int $userId): bool;

    /**
     * Permanently delete all deleted flashcards for a user.
     */
    public function forceDeleteAllForUser(int $userId): bool;
}
