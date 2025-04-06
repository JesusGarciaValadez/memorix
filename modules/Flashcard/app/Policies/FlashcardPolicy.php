<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Flashcard\app\Models\Flashcard;

final class FlashcardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any flashcards.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the flashcard.
     */
    public function view(User $user, Flashcard $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can create flashcards.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the flashcard.
     */
    public function update(User $user, Flashcard $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can delete the flashcard.
     */
    public function delete(User $user, Flashcard $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can restore the flashcard.
     */
    public function restore(User $user, Flashcard $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }

    /**
     * Determine whether the user can permanently delete the flashcard.
     */
    public function forceDelete(User $user, Flashcard $flashcard): bool
    {
        return $user->id === $flashcard->user_id;
    }
}
