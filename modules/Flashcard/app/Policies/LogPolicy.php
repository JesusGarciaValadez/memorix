<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Flashcard\app\Models\Log;

final class LogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any logs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the log.
     */
    public function view(User $user, Log $log): bool
    {
        return $user->id === $log->user_id;
    }

    /**
     * Determine whether the user can create logs.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the log.
     */
    public function update(User $user, Log $log): bool
    {
        // Logs should not be updated after creation
        return false;
    }

    /**
     * Determine whether the user can delete the log.
     */
    public function delete(User $user, Log $log): bool
    {
        // Regular users should not be able to delete logs
        // This could be extended to allow admins to delete logs
        return false;
    }

    /**
     * Determine whether the user can view their own activity log.
     */
    public function viewActivity(User $user): bool
    {
        return true;
    }
}
