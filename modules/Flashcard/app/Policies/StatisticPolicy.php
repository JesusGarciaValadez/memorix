<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Flashcard\app\Models\Statistic;

final class StatisticPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any statistics.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the statistic.
     */
    public function view(User $user, Statistic $statistic): bool
    {
        return $user->id === $statistic->user_id;
    }

    /**
     * Determine whether the user can create statistics.
     */
    public function create(): bool
    {
        // Statistics are typically created automatically
        // This might be restricted to system processes
        return false;
    }

    /**
     * Determine whether the user can update the statistic.
     */
    public function update(): bool
    {
        // Statistics should be updated through the increment methods
        // Direct updates might be restricted
        return false;
    }

    /**
     * Determine whether the user can delete the statistic.
     */
    public function delete(): bool
    {
        // Statistics should generally not be deleted
        return false;
    }

    /**
     * Determine whether the user can reset their own statistics.
     */
    public function reset(User $user, Statistic $statistic): bool
    {
        return $user->id === $statistic->user_id;
    }
}
