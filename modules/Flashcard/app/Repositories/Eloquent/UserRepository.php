<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories\Eloquent;

use App\Models\User;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get user password by email.
     */
    public function getPasswordByEmail(string $email): ?string
    {
        return User::where('email', $email)->pluck('password')->first();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }
}
