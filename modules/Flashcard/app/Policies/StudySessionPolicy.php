<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Flashcard\app\Models\StudySession;

final class StudySessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any study sessions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the study session.
     */
    public function view(User $user, StudySession $studySession): bool
    {
        return $user->id === $studySession->user_id;
    }

    /**
     * Determine whether the user can create study sessions.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the study session.
     */
    public function update(User $user, StudySession $studySession): bool
    {
        return $user->id === $studySession->user_id;
    }

    /**
     * Determine whether the user can delete the study session.
     */
    public function delete(User $user, StudySession $studySession): bool
    {
        // Study sessions should generally not be deleted once created
        return false;
    }

    /**
     * Determine whether the user can end the study session.
     */
    public function end(User $user, StudySession $studySession): bool
    {
        return $user->id === $studySession->user_id && $studySession->isActive();
    }

    /**
     * Determine whether the user can view statistics for the study session.
     */
    public function viewStatistics(User $user, StudySession $studySession): bool
    {
        return $user->id === $studySession->user_id;
    }
}
