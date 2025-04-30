<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;

final class FlashcardRepository implements FlashcardRepositoryInterface
{
    /**
     * Get all flashcards for a user.
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Flashcard::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all deleted flashcards for a user.
     */
    public function getAllDeletedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Flashcard::onlyTrashed()
            ->where('user_id', $userId)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a flashcard for a user.
     */
    public function findForUser(int $flashcardId, int $userId, bool $withTrashed = false): ?Flashcard
    {
        $query = Flashcard::where('id', $flashcardId)
            ->where('user_id', $userId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Create a new flashcard.
     */
    public function create(array $data): Flashcard
    {
        return Flashcard::create($data);
    }

    /**
     * Update a flashcard.
     */
    public function update(Flashcard $flashcard, array $data): bool
    {
        return $flashcard->update($data);
    }

    /**
     * Delete a flashcard (soft delete).
     */
    public function delete(Flashcard $flashcard): bool
    {
        return (bool) $flashcard->delete();
    }

    /**
     * Restore a deleted flashcard.
     */
    public function restore(Flashcard $flashcard): bool
    {
        return $flashcard->restore();
    }

    /**
     * Permanently delete a flashcard.
     */
    public function forceDelete(Flashcard $flashcard): bool
    {
        return (bool) $flashcard->forceDelete();
    }

    /**
     * Restore all deleted flashcards for a user.
     */
    public function restoreAll(int $userId): bool
    {
        $result = Flashcard::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        return $result > 0;
    }

    /**
     * Permanently delete all deleted flashcards for a user.
     */
    public function forceDeleteAll(int $userId): bool
    {
        $result = Flashcard::onlyTrashed()
            ->where('user_id', $userId)
            ->forceDelete();

        return $result > 0;
    }
}
